<?php

namespace App\Http\Controllers;

use App\Models\WasteRequest;
use App\Models\WasteRequestImage;
use App\Models\User;
use App\Models\Ward;
use App\Models\CityCorporation;
use App\Models\Employee;
use Illuminate\Http\Request;
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
            $query = WasteRequest::with('user', 'ward')->latest();

            // If user has role 'Citizen', filter by their user_id
            if (auth()->user()->hasRole('Citizen')) {
                $query->where('user_id', auth()->id());
            }

            $data = $query->get();

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('citizen_name', function ($row) {
                    return $row->user?->name ?? 'N/A';
                })
                ->addColumn('ward_name', function ($row) {
                    return $row->ward?->name ?? 'N/A';
                })
                ->addColumn('pickup_schedule', function ($row) {
                    return $row->pickup_date . ' (' . ucfirst($row->pickup_time ?? 'N/A') . ')';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = match ($row->status) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'assigned' => 'primary',
                        'collected' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {

                    $buttons = '';
                
                    // VIEW BUTTON (everyone with access)
                    $buttons .= '
                        <a href="' . route('waste-requests.show', ['waste_request' => $row->id]) . '" 
                        class="btn btn-sm btn-primary mb-1">
                            <i class="ri-eye-fill"></i>
                        </a>
                    ';
                
                    // ASSIGN BUTTON (permission + status check)
                    if (auth()->user()->can('assign waste request') && in_array($row->status, ['pending','approved'])) {
                        $buttons .= '
                            <a href="' . route('waste-requests.assignPage', $row->id) . '" 
                            class="btn btn-sm btn-warning mb-1">
                                <i class="ri-user-add-line"></i> Assign
                            </a>
                        ';
                    }
                
                    // ACTION BUTTON (status update)
                    if (auth()->user()->can('update waste request')) {
                        $buttons .= '
                            <a href="' . route('waste-requests.actionPage', $row->id) . '" 
                            class="btn btn-sm btn-info mb-1">
                                <i class="ri-settings-4-line"></i> Action
                            </a>
                        ';
                    }
                
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('waste_requests.index');
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
        // CREATE main waste request
        $wasteRequest = WasteRequest::create([
            'city_corporation_id' => $request->city_corporation_id,
            'ward_id' => $request->ward_id,
            'waste_type' => $request->waste_type,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'estimated_weight' => $request->estimated_weight,
            'hazardous' => $request->hazardous ? 1 : 0,
            'waste_description' => $request->waste_description,
            'pickup_date' => $request->pickup_date,
            'status' => 'pending',
            'user_id' => auth()->user()->id,
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
    public function assign(Request $request, $id)
    {
        
        $request->validate([
            'collector_id' => 'required|exists:users,id',
        ]);
        $wasteRequest = WasteRequest::findOrFail($id);
        $collector = User::findOrFail($request->collector_id);
       
        $wasteRequest->update([
            'assigned_to' => $collector->id,
            'status' => 'assigned',
        ]);

        return redirect()
            ->route('waste-requests.show', $id)
            ->with('success', 'Waste request assigned successfully!');
    }

    /**
     * Mark a waste request as completed.
     */
    public function complete($id)
    {
        $wasteRequest = WasteRequest::findOrFail($id);
        $wasteRequest->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);

        return redirect()
            ->route('waste-requests.show', $id)
            ->with('success', 'Waste request marked as completed!');
    }

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
        'status' => 'required|in:pending,approved,assigned,in-progress,completed,cancelled'
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





}
