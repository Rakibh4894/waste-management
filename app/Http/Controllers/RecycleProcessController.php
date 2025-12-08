<?php

namespace App\Http\Controllers;

use App\Helpers\EmailHelper;
use App\Models\RecycleItem;
use App\Models\RecycleProcess;
use App\Models\User;
use App\Models\WasteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Auth;

class RecycleProcessController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = RecycleProcess::with(['wasteRequest', 'sortingOfficer', 'recyclingOperator'])->latest();

            $data = $query->get();

            return datatables()->of($data)
                ->addColumn('waste_id', function($row){
                    return str_pad($row->waste_request_id, 4, '0', STR_PAD_LEFT);
                })
                ->addColumn('citizen', function($row){
                    return $row->wasteRequest?->user?->name ?? 'N/A';
                })
                ->addColumn('waste_type', function($row){
                    return ucfirst($row->wasteRequest?->waste_type ?? 'N/A');
                })
                ->addColumn('estimated_weight', function($row){
                    return $row->wasteRequest?->estimated_weight ? $row->wasteRequest->estimated_weight . ' kg' : 'N/A';
                })
                ->addColumn('recycle_status_badge', function($row){
                    $colors = [
                        'pending' => 'secondary',
                        'recycled' => 'success',
                        'cancelled' => 'danger',
                    ];
                    $color = $colors[$row->recycle_status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst(str_replace('_', ' ', $row->recycle_status)).'</span>';
                })
                ->addColumn('action', function($row){
                    $buttons = '';
                    $buttons .= '<a href="'.route('recycle-process.show', $row->id).'" class="btn btn-sm btn-primary mb-1">
                                    <i class="ri-eye-fill"></i>
                                </a>';

                    // CANCEL BUTTON (modal)
                    if (auth()->user()->can('RP_CANCEL') && !in_array($row->recycle_status, ['recycled', 'cancelled'])) {
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
                                            <h5 class="modal-title">Cancel Recycling</h5>
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

                    if (auth()->user()->can('RP_RECYCLE') && !in_array($row->recycle_status, ['recycled', 'cancelled'])) {
                        $buttons .= '
                            <a href="'.route('recycle-process.completeRecycling', $row->id).'"
                                title="Recycle"
                                class="btn btn-sm btn-primary mb-1">
                                <i class="fa fa-recycle"></i>
                            </a>
                        ';
                    }

                    return $buttons;
                })
                ->rawColumns(['recycle_status_badge', 'action'])
                ->make(true);
        }

        return view('recycle_process.index');
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
        $rpRequest = RecycleProcess::findOrFail($id);

        // Update status and reason
        $rpRequest->recycle_status = 'cancelled';
        $rpRequest->cancel_reason = $request->reason; // make sure this column exists
        $rpRequest->save();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Recycle Process cancelled successfully.'
        ]);
    }


    public function completeRecycling(Request $request, $id)
    {
        $request = RecycleProcess::findOrFail($id);
        return view('recycle_process.complete', compact('request', 'id'));
    }
    public function completeUpdate(Request $request, $id)
    {
        // Validate base fields
        $request->validate([
            'item_type' => 'required|array',
            'item_name' => 'required|array',
            'weight'    => 'required|array',
            'quantity'  => 'required|array',
            'value'     => 'nullable|array',
            'note'      => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {

            // 1️⃣ Update recycle_process status
            $process = RecycleProcess::findOrFail($id);
            $process->recycle_status = 'recycled';
            $process->recycling_completed_at = now();
            $process->save();

            // 2️⃣ Delete existing items (if any)
            RecycleItem::where('recycle_process_id', $id)->delete();

            // 3️⃣ Insert new items
            $itemTypes = $request->item_type;
            $itemNames = $request->item_name;
            $weights   = $request->weight;
            $qtys      = $request->quantity;
            $values    = $request->value ?? [];
            $notes     = $request->note ?? [];

            $insertData = [];

            for ($i = 0; $i < count($itemTypes); $i++) {
                $insertData[] = [
                    'recycle_process_id' => $id,
                    'item_type'   => $itemTypes[$i],
                    'item_name'   => $itemNames[$i],
                    'weight'      => $weights[$i],
                    'quantity'    => $qtys[$i],
                    'value'       => $values[$i] ?? null,
                    'notes'        => $notes[$i] ?? null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            RecycleItem::insert($insertData);


            // Upload images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {

                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('uploads/recycle-images'), $filename);

                    $process->images()->create([
                        'image_path' => 'uploads/recycle-images/' . $filename
                    ]);
                }
            }

            DB::commit();

            // EmailHelper::send('rakib.hasan0408@gmail.com', "Request Submission", "Your waste request is submitted. Ref. No #{$id}");
            // SmsHelper::send($user->phone, "Your waste request is submitted. Ref. No #{$id}");

            return redirect()
                ->route('recycle-process.index')
                ->with('success', 'Recycle process marked as completed successfully.');

        } catch (\Exception $e) {
            dd(''. $e->getMessage());
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $process = RecycleProcess::with([
            'wasteRequest',
            'wasteRequest.user',
            'recycleItems'
        ])->findOrFail($id);

        return view('recycle_process.show', compact('process'));
    }

}
