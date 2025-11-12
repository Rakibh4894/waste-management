<?php

namespace App\Http\Controllers;

use App\Models\WasteRequest;
use App\Models\WasteRequestImage;
use App\Models\User;
use App\Models\Ward;
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
        $data = WasteRequest::with('user', 'ward')->latest()->get();

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
                // ✅ Pass the whole model instance for route model binding
                return '
                    <a href="' . route('waste-requests.show', ['waste_request' => $row->id]) . '" class="btn btn-sm btn-primary">
                        <i class="ri-eye-fill"></i> View
                    </a>
                ';
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
        $regions = Ward::all();
        return view('waste_requests.create', compact('regions'));
    }

    /**
     * Store a new waste request (non-AJAX form submission).
     */
    public function store(Request $request)
    {
        $request->validate([
            'waste_type' => 'required|string',
            'waste_description' => 'nullable|string',
            'estimated_weight' => 'nullable|numeric',
            'hazardous' => 'nullable|boolean',
            'region_id' => 'required|exists:wards,id',
            'zone_name' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pickup_date' => 'required|date',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Create the waste request
        $wasteRequest = WasteRequest::create([
            'user_id' => Auth::id(),
            'request_date' => now(),
            'waste_type' => $request->waste_type,
            'waste_description' => $request->waste_description,
            'estimated_weight' => $request->estimated_weight,
            'hazardous' => $request->hazardous ?? 0,
            'region_id' => $request->region_id,
            'zone_name' => $request->zone_name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'pickup_date' => $request->pickup_date,
            'status' => 'pending',
        ]);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('waste_images', 'public');
                $wasteRequest->images()->create(['image_path' => $path]);
            }
        }

        return redirect()
            ->route('waste-requests.index')
            ->with('success', 'Waste request submitted successfully!');
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
            'collector_id' => 'required|exists:employees,id',
        ]);

        $wasteRequest = WasteRequest::findOrFail($id);
        $collector = Employee::findOrFail($request->collector_id);

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
}
