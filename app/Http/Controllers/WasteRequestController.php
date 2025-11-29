<?php

namespace App\Http\Controllers;

use App\Helpers\EmailHelper;
use App\Helpers\SmsHelper;
use App\Models\WasteRequest;
use App\Models\WasteRequestImage;
use App\Models\User;
use App\Models\Ward;
use App\Models\CityCorporation;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class WasteRequestController extends Controller
{
    /**
     * Display the list of waste requests with DataTable.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Start query
            $query = WasteRequest::with(['user', 'ward', 'cityCorporation', 'assignedTo'])->latest();

            $user = Auth::user();

            $priority = $request->query('priority');
            $hazardous = $request->query('hazardous');
            $largeQuantity = $request->query('large_quantity');
            $longPending = $request->query('long_pending');
            $wasteType = $request->query('waste_type');

            if($hazardous !== null && $hazardous !== '') {
                $query->where('hazardous', $hazardous);
            }

            if($largeQuantity) {
                $query->where('estimated_weight', '>=', $largeQuantity);
            }

            if($longPending) {
                $query->whereDate('created_at', '<=', now()->subDays($longPending));
            }

            if($wasteType) {
                $query->where('waste_type', $wasteType);
            }

            if($priority) {
                $query->where('priority', $priority);
            }
            
            if (auth()->user()->hasRole('Citizen')) {
                $query->where('user_id', auth()->id());
            }elseif ($user->hasRole('Collector')) {
                $query->where('assigned_to', $user->id);
            }
            elseif ($user->hasRole('Admin')) {
                if (!empty($user->ward_id)) {
                    $query->where('ward_id', $user->ward_id);
                } else {
                    $query->where('city_corporation_id', $user->city_corporation_id);
                }
            }

            $data = $query->get();

            return datatables()->of($data)

                // Citizen Name
                ->addColumn('id', function ($row) {
                    return str_pad($row->id, 4, '0', STR_PAD_LEFT);
                })
                
                // Citizen Name
                ->addColumn('citizen_name', function ($row) {
                    return $row->user?->name ?? 'N/A';
                })

                // Address
                ->addColumn('address', function ($row) {
                    return $row->address ?? 'N/A';
                })

                // Ward Name
                ->addColumn('ward_name', function ($row) {
                    return $row->ward?->number ?? 'N/A';
                })

                // City Corporation Name
                ->addColumn('city_corporation_name', function ($row) {
                    return $row->cityCorporation?->title ?? 'N/A';
                })

                // Waste Type
                ->addColumn('waste_type', function ($row) {
                    return ucfirst($row->waste_type);
                })

                // Estimated Weight
                ->addColumn('estimated_weight', function ($row) {
                    return $row->estimated_weight ? $row->estimated_weight . ' kg' : 'N/A';
                })

                // Hazardous Badge
                ->addColumn('hazardous_badge', function ($row) {
                    return $row->hazardous
                        ? '<span class="badge bg-danger">Yes</span>'
                        : '<span class="badge bg-success">No</span>';
                })
                ->rawColumns(['hazardous_badge'])

                // Pickup Schedule
                ->addColumn('pickup_schedule', function ($row) {
                    return $row->request_date ? Carbon::parse($row->request_date)->format('d M Y, h:i A') : '';
                })

                // Assigned To
                ->addColumn('assigned_to_name', function ($row) {
                    return $row->assignedTo?->name ?? '';
                })

                // Status Badge
                ->addColumn('status_badge', function ($row) {
                    $color = match ($row->status) {
                        'pending' => 'warning',
                        'assigned' => 'primary',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };
                    return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
                })
                ->rawColumns(['status_badge'])

                // Request Date
                ->addColumn('request_date', function ($row) {
                    return $row->request_date ? Carbon::parse($row->request_date)->format('d M Y, h:i A') : '';
                })
                ->addColumn('action', function ($row) {

                    $buttons = '';

                    // VIEW BUTTON
                    $buttons .= '<a href="'.route('waste-requests.show', $row->id).'" class="btn btn-sm btn-primary mb-1">
                                    <i class="ri-eye-fill"></i>
                                </a>';

                    // ASSIGN BUTTON (modal)
                    if (auth()->user()->can('WR_ASSIGN') && in_array($row->status, ['pending']) && !in_array($row->status, ['cancelled'])) {
                        $buttons .= '
                            <button type="button" title="Assign" class="btn btn-sm btn-warning mb-1" onclick="openAssignModal('.$row->id.')">
                                <i class="ri-user-add-line"></i>
                            </button>

                            <div class="modal fade" id="assignModal'.$row->id.'" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form class="assignForm" data-id="'.$row->id.'">
                                            '.csrf_field().'
                                            <div class="modal-header">
                                                <h5 class="modal-title">Assign Collector</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 text-start">
                                                    <label for="collector_id" class="text-start">Collector</label>
                                                    <select name="collector_id" class="form-select" required>
                                                        '.self::collectorsWithLessThanTenAssignmentsOptionsHtml().'
                                                    </select>
                                                </div>
                                                <div class="mb-3 text-start">
                                                    <label for="priority" class="text-start">Priority</label>
                                                    <select name="priority" class="form-select" required>
                                                        <option value="normal" '.($row->priority == 'normal' ? 'selected' : '').'>Normal</option>
                                                        <option value="low" '.($row->priority == 'low' ? 'selected' : '').'>Low</option>
                                                        <option value="high" '.($row->priority == 'high' ? 'selected' : '').'>High</option>
                                                        <option value="urgent" '.($row->priority == 'urgent' ? 'selected' : '').'>Urgent</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-warning">Assign</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        ';

                    }

                    // CANCEL BUTTON (modal)
                    if (auth()->user()->can('WR_CANCEL') && in_array($row->status, ['pending'])) {
                        $buttons .= '
                            <button type="button" title="Cancel"  class="btn btn-sm btn-danger mb-1" onclick="openCancelModal('.$row->id.')">
                                <i class="ri-close-circle-line"></i>
                            </button>

                            <div class="modal fade" id="cancelModal'.$row->id.'" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form class="cancelForm" data-id="'.$row->id.'">
                                        '.csrf_field().'
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cancel Waste Request</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label>Reason for Cancellation</label>
                                            <textarea name="reason" class="form-control" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </div>
                        ';
                    }

                    // START TASK BUTTON (assigned collector only, status = assigned)
                    if (auth()->user()->hasRole('Collector') && $row->status == 'assigned' && $row->assigned_to == auth()->id()) {
                        $buttons .= '
                            <form method="POST" action="'.route('waste-requests.startTask', $row->id).'" class="d-inline startTaskForm">
                                '.csrf_field().'
                                <button type="button" title="Start Collecting" class="btn btn-sm btn-primary mb-1 startTaskBtn">
                                    <i class="ri-play-line"></i>
                                </button>
                            </form>
                        ';
                    }

                    // COMPLETE BUTTON (modal)
                    if (auth()->user()->can('WR_COMPLETE') && $row->status == 'in_progress') {
                        $buttons .= '
                            <button type="button" title="Complete" class="btn btn-sm btn-success mb-1" onclick="openCompleteModal('.$row->id.')">
                                <i class="ri-check-line"></i>
                            </button>

                            <div class="modal fade" id="completeModal'.$row->id.'" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form class="completeForm" data-id="'.$row->id.'">
                                        '.csrf_field().'
                                        <div class="modal-header">
                                            <h5 class="modal-title">Complete Waste Request</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label>Remarks</label>
                                            <textarea name="remarks" class="form-control" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-success">Complete</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            </div>
                        ';
                    }

                    return $buttons;
                })
                ->rawColumns(['hazardous_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('waste_requests.index');
    }

    private function collectorsWithLessThanTenAssignmentsOptionsHtml()
    {
        $collectors = User::role('collector')->get();
        $html = '';

        foreach ($collectors as $collector) {
            $count = WasteRequest::where('assigned_to', $collector->id)
                        ->whereIn('status', ['assigned','in-progress'])->count();
            if ($count < 10) {
                $html .= '<option value="'.$collector->id.'">'.$collector->name.' ('.$count.' in hand)</option>';
            }
        }

        return $html;
    }


    

    /**
     * Show the form for creating a new waste request.
     */
    public function create()
    {
        $cityCorporations = CityCorporation::all();
        return view('waste_requests.create', compact('cityCorporations'));
    }

    /**
     * Store a new waste request (non-AJAX form submission).
     */
    public function store(Request $request)
    {
        $request->validate([
            'city_corporation_id' => 'required',
            'ward_id' => 'required|exists:wards,id',
            'waste_type' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'estimated_weight' => 'nullable|numeric',
            'hazardous' => 'nullable|boolean',
            'waste_description' => 'nullable|string',
            'pickup_date' => 'required|date',
            'images.*' => 'nullable|image|max:2048',
        ]);
        $user_id = auth()->user()->id;
        
        $wasteRequest = WasteRequest::create([
            'city_corporation_id' => $request->city_corporation_id,
            'ward_id' => $request->ward_id,
            'waste_type' => $request->waste_type,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'estimated_weight' => $request->estimated_weight,
            'priority' => $request->priority,
            'hazardous' => $request->hazardous ? 1 : 0,
            'waste_description' => $request->waste_description,
            'pickup_date' => $request->pickup_date,
            'status' => 'pending',
            'user_id' => $user_id,
        ]);

        // UPLOAD IMAGES (if any)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/waste-requests'), $filename);

                $wasteRequest->images()->create([
                    'image_path' => 'uploads/waste-requests/' . $filename
                ]);
            }
        }

        $id = $wasteRequest->id;
        $user = User::find($wasteRequest->user_id);

        EmailHelper::send('rakib.hasan0408@gmail.com', "Request Submission", "Your waste request is submitted. Ref. No #{$id}");
        // SmsHelper::send($user->phone, "Your waste request is submitted. Ref. No #{$id}");

        return redirect()->route('waste-requests.index')
                        ->with('success', 'Waste Request Submitted Successfully!');
    }

    /**
     * Display the specified waste request details.
     */
    public function show($id)
    {
        $data = WasteRequest::with(['user', 'images', 'assignedEmployee', 'ward'])
            ->findOrFail($id);

        return view('waste_requests.show', compact('data'));
    }

    /**
     * Assign a waste collector or team to the request.
     */
    

    /**
     * Mark a waste request as completed.
     */


    public function getWards($city_corporation_id)
    {
        $wards = Ward::where('city_corporation_id', $city_corporation_id)->get();

        return response()->json($wards);
    }

    public function assignPage($id)
    {
        $wasteRequest = WasteRequest::findOrFail($id);

        // Get only users who have the "Collector" role
        $collectors = \App\Models\User::role('Collector')->get();

        return view('waste_requests.assign', compact('wasteRequest', 'collectors'));
    }

    public function actionPage($id)
    {
        $wasteRequest = WasteRequest::findOrFail($id);

        return view('waste_requests.action', compact('wasteRequest'));
    }

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,assigned,in-progress,completed,cancelled'
    ]);

    $wasteRequest = WasteRequest::findOrFail($id);
    $wasteRequest->update([
        'status' => $request->status,
    ]);

    return redirect()
        ->route('waste-requests.show', $id)
        ->with('success', 'Status updated successfully!');
}

public function collectorInProgress($collectorId)
{
    $collector = User::findOrFail($collectorId);

    $count = WasteRequest::where('assigned_to', $collectorId)
                         ->where('status', 'assigned')   // in progress type
                         ->count();

    return response()->json([
        'name' => $collector->name,
        'count' => $count
    ]);
}

public function cancel(Request $request, $id)
{
    // Validate input manually
    $validator = Validator::make($request->all(), [
        'reason' => 'required|string|max:255',
    ]);

    // If validation fails, return JSON with errors
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the waste request
    $wasteRequest = WasteRequest::findOrFail($id);

    // Update status and reason
    $wasteRequest->status = 'cancelled';
    $wasteRequest->cancel_reason = $request->reason; // make sure this column exists
    $wasteRequest->save();

    // Return success response
    return response()->json([
        'success' => true,
        'message' => 'Waste request cancelled successfully.'
    ]);
}


public function assign(Request $request, $id)
{
    // Validate input manually
    $validator = Validator::make($request->all(), [
        'collector_id' => 'required',
        'priority' => 'required',
    ]);

    // If validation fails, return JSON with errors
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the waste request
    $wasteRequest = WasteRequest::findOrFail($id);
    $wasteRequest->status = 'assigned';
    $wasteRequest->priority = $request->priority;
    $wasteRequest->assigned_to = $request->collector_id;
    $wasteRequest->save();

    // Return success response
    return response()->json([
        'success' => true,
        'message' => 'Waste request assigned successfully.'
    ]);
}

public function complete(Request $request, $id)
{
    // Validate input manually
    $validator = Validator::make($request->all(), [
        
    ]);

    // If validation fails, return JSON with errors
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the waste request
    $wasteRequest = WasteRequest::findOrFail($id);
    $wasteRequest->status = 'completed';
    $wasteRequest->complete_remarks = $request->remarks;
    $wasteRequest->save();

    // Return success response
    return response()->json([
        'success' => true,
        'message' => 'Waste request completed successfully.'
    ]);
}

public function startTask(Request $request, $id)
{
    $wasteRequest = WasteRequest::findOrFail($id);

    // Only assigned collector can start
    if ($wasteRequest->assigned_to != auth()->id()) {
        return response()->json(['success' => false, 'message' => 'Not authorized.']);
    }

    $wasteRequest->status = 'in_progress';
    $wasteRequest->save();

    return response()->json(['success' => true, 'message' => 'Task started successfully.']);
}








}
