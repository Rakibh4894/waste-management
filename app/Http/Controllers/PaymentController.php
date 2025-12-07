<?php

namespace App\Http\Controllers;

use App\Models\CityCorporation;
use App\Models\Payment;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Brian2694\Toastr\Facades\Toastr;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = Payment::with('user', 'ward', 'cityCorporation')->orderBy('payment_month', 'desc');

            if (auth()->user()->hasRole('Citizen')) {
                $query->where('user_id', auth()->id());
            } elseif ($user->hasRole('Admin')) {
                if (!empty($user->ward_id)) {
                    $query->where('ward_id', $user->ward_id);
                } else {
                    $query->where('city_corporation_id', $user->city_corporation_id);
                }
            }

            $data = $query->get();

            return datatables()->of($data)
                ->addColumn('id', function ($row) {
                    return str_pad($row->id, 4, '0', STR_PAD_LEFT);
                })
                ->addColumn('citizen_name', function ($row) {
                    return $row->user?->name ?? 'N/A';
                })
                ->addColumn('city_corporation_name', function ($row) {
                    return $row->cityCorporation?->title ?? 'N/A';
                })
                ->addColumn('ward_name', function ($row) {
                    return $row->ward?->number ?? 'N/A';
                })
                ->addColumn('payment_month', function ($row) {
                    return $row->payment_month ? Carbon::parse($row->payment_month)->format('M Y') : 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return 'Tk ' . number_format($row->amount, 2);
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d M Y') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';

                    // HTML view
                    $buttons .= '<a href="' . route('payments.view', $row->id) . '" class="btn btn-sm btn-primary mb-1" title="View" target="_blank">'
                        . '<i class="ri-eye-fill"></i></a> ';

                    // Pay button for unpaid
                    if ($row->status !== 'paid') {
                        $buttons .= '<a href="' . route('payments.proceed') . '" class="btn btn-sm btn-success mb-1" title="Pay">'
                            . '<i class="ri-bank-card-fill"></i></a>';
                    }

                    // PDF for paid
                    if ($row->status === 'paid') {
                        $buttons .= ' <a href="' . route('payments.receipt', $row->id) . '" class="btn btn-sm btn-info mb-1" title="Download Receipt" target="_blank">'
                            . '<i class="ri-download-2-fill"></i></a>';
                    }

                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('payments.index');
    }

    public function create()
    {
        $cityCorporations = CityCorporation::all();
        $user = Auth::user();
        $cityCorporationName = "";
        $wardNumber = "";
        if ($user->ward_id > 0) {
            $ward = Ward::find($user->ward_id);
            $wardNumber = $ward->number;
            $cityCorporation = CityCorporation::find($ward->city_corporation_id);
            $cityCorporationName = $cityCorporation->title;
        }

        return view('payments.create', compact('cityCorporations', 'cityCorporationName', 'wardNumber', 'user'));
    }

    // create/store a bill (admin action) — single bill
    public function store(Request $request)
    {
        $this->authorize('create', Payment::class); // optional gate/policy

        $data = $request->validate([
            'user_id' => ['required'],
            'ward_id' => ['required'],
            'city_corporation_id' => ['required'],
            'payment_month' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        // normalize payment_month to first day of month
        $data['payment_month'] = Carbon::parse($data['payment_month'])->startOfMonth()->toDateString();

        // prevent duplication
        if (Payment::where('user_id', $data['user_id'])->where('payment_month', $data['payment_month'])->exists()) {
            Toastr::error('A bill for this user and month already exists.');
            return back();
        }

        Payment::create($data);

        Toastr::success('Payment submitted successfully!');
        return back();
    }

    // Mark a bill as paid (called by payment gateway webhook or admin)
    public function markPaid(Request $request, Payment $bill)
    {
        $this->authorize('update', $bill); // optional

        $data = $request->validate([
            'payment_method' => ['nullable', 'string'],
            'payment_reference' => ['nullable', 'string'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $bill->update([
            'status' => 'paid',
            'payment_method' => $data['payment_method'] ?? $bill->payment_method,
            'payment_reference' => $data['payment_reference'] ?? $bill->payment_reference,
            'paid_at' => $data['paid_at'] ? Carbon::parse($data['paid_at']) : now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Payment marked paid.']);
    }

    public function show($id)
    {
        $data = Payment::with(['user', 'images', 'assignedEmployee', 'ward'])
            ->findOrFail($id);

        return view('waste_requests.show', compact('data'));
    }

    public function proceed(Request $request)
    {
        $data = $request->all();
        // Normalize bill month if provided
        $billMonth = null;
        if (!empty($data['bill_month'])) {
            try {
                $billMonth = Carbon::parse($data['bill_month'])->startOfMonth()->toDateString();
            } catch (\Exception $e) {
                $billMonth = null;
            }
        }

        // If an ID was not provided, try to find an existing bill for this user+month
        if (!isset($data['id'])) {
            if ($billMonth) {
                $existing = Payment::where('user_id', auth()->id())
                    ->where('payment_month', $billMonth)
                    ->first();

                if ($existing) {
                    // If already paid, show a friendly error
                    if ($existing->status === 'paid') {
                        Session::flash('error', 'You have already paid for ' . Carbon::parse($billMonth)->format('F Y'));
                        return redirect()->back()->with('error', 'You have already paid for ' . Carbon::parse($billMonth)->format('F Y') . '.');
                    }

                    // If pending (or other), reuse the existing record and show the pay modal/page
                    $data = [
                        'id' => $existing->id,
                        'ward_id' => $existing->ward_id,
                        'city_corporation_id' => $existing->city_corporation_id,
                        'bill_month' => $billMonth,
                        'amount' => $existing->amount,
                    ];

                    return view('payments.pay', compact('data'));
                }
            }

            // No existing payment found — create a new pending payment
            try {
                $payment = Payment::create([
                    'user_id' => auth()->id(),
                    'ward_id' => $data['ward_id'] ?? null,
                    'city_corporation_id' => $data['city_corporation_id'] ?? null,
                    'payment_month' => $billMonth,
                    'amount' => $data['amount'] ?? 0,
                    'status' => 'pending',
                ]);
                $data['id'] = $payment->id;
            } catch (QueryException $e) {
                // Handle possible race-condition duplicate key insertion gracefully
                if ($e->getCode() == '23000') {
                    $existing = Payment::where('user_id', auth()->id())
                        ->where('payment_month', $billMonth)
                        ->first();

                    if ($existing) {
                        if ($existing->status === 'paid') {
                            return redirect()->back()->with('error', 'You have already paid for ' . Carbon::parse($billMonth)->format('F Y') . '.');
                        }

                        $data = [
                            'id' => $existing->id,
                            'ward_id' => $existing->ward_id,
                            'city_corporation_id' => $existing->city_corporation_id,
                            'bill_month' => $billMonth,
                            'amount' => $existing->amount,
                        ];

                        return view('payments.pay', compact('data'));
                    }
                }

                \Log::error('Payment create error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Unable to create payment. Please try again.');
            }
        }

        return view('payments.pay', compact('data'));
    }

    public function processPayment(Request $request)
    {
        try {
            $data = $request->validate([
                'payment_id' => ['required', 'integer', 'exists:payments,id'],
                'payment_method' => ['required', 'string', Rule::in(['bkash', 'nagad', 'card'])],
                'mobile' => ['nullable', 'string'],
                'otp' => ['nullable', 'string'],
                'pin' => ['nullable', 'string'],
                'card_number' => ['nullable', 'string'],
                'expiry' => ['nullable', 'string'],
                'cvv' => ['nullable', 'string'],
            ]);

            // Get payment from request
            $payment = Payment::findOrFail($data['payment_id']);

            // Verify user owns this payment
            if ($payment->user_id !== auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Update payment with all transaction details
            $payment->update([
                'status' => 'paid',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $this->generatePaymentReference($data),
                'paid_at' => now(),
            ]);

            // Store transaction log
            \App\Models\MessageLog::create([
                'user_id' => auth()->id(),
                'type' => 'payment',
                'message' => json_encode([
                    'payment_id' => $payment->id,
                    'method' => $data['payment_method'],
                    'amount' => $payment->amount,
                    'mobile' => $data['mobile'] ?? null,
                    'card_last_four' => $data['card_number'] ? substr($data['card_number'], -4) : null,
                ]),
            ]);

            return response()->json(['success' => true, 'message' => 'Payment processed successfully!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Payment error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'string'],
            'otp' => ['required', 'string'],
            'payment_method' => ['required', 'string', Rule::in(['bkash', 'nagad'])],
        ]);

        // Verify OTP (for demo, accept any 6-digit OTP)
        if (strlen($data['otp']) != 6 || !is_numeric($data['otp'])) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP format. Please enter a 6-digit OTP.']);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified successfully!']);
    }

    private function generatePaymentReference($data)
    {
        $method = $data['payment_method'];
        $identifier = '';

        if ($method === 'bkash' || $method === 'nagad') {
            $identifier = substr($data['mobile'] ?? '', -4);
        } elseif ($method === 'card') {
            $identifier = substr($data['card_number'] ?? '', -4);
        }

        return strtoupper($method) . '-' . date('YmdHis') . '-' . $identifier;
    }

    /**
     * Generate and return a PDF receipt for a payment.
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['user', 'ward', 'cityCorporation']);

        // Authorization: owners and admins can view
        if ($payment->user_id !== auth()->id() && !auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $data = ['payment' => $payment];

        $pdf = Pdf::loadView('payments.receipt', $data)->setPaper('a4', 'portrait');

        $filename = 'payment_receipt_' . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show an HTML view of the receipt (for quick preview in browser).
     */
    public function viewReceipt(Payment $payment)
    {
        $payment->load(['user', 'ward', 'cityCorporation']);

        if ($payment->user_id !== auth()->id() && !auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        return view('payments.view', ['payment' => $payment]);
    }

}