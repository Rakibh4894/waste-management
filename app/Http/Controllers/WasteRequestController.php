<?php

namespace App\Http\Controllers;

use App\Models\WasteRequest;
use App\Models\WasteRequestImage;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class WasteRequestController extends Controller
{
    /**
     * Show list page with DataTable
     */
    public function index(Request $request)
    {
        // Handle AJAX for Yajra DataTable
        if ($request->ajax()) {
            $data = WasteRequest::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('pickup_schedule', function ($row) {
                    return $row->pickup_date . ' (' . $row->pickup_time . ')';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = match ($row->status) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'collected' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <a href="' . route('waste-requests.show', $row->id) . '" class="btn btn-sm btn-primary">View</a>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        // If not AJAX, load the main page
        return view('waste_requests.index');
    }

    /**
     * Show form for creating new request
     */
    public function create()
    {
        return view('waste_requests.create');
    }

    /**
     * Store waste request (non-AJAX submission)
     */
    public function store(Request $request)
    {
        $request->validate([
            'waste_type' => 'required|string',
            'quantity' => 'nullable|string',
            'description' => 'nullable|string',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Create the waste request
        $wasteRequest = WasteRequest::create([
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'waste_type' => $request->waste_type,
            'quantity' => $request->quantity,
            'description' => $request->description,
            'pickup_date' => $request->pickup_date,
            'pickup_time' => $request->pickup_time,
            'notes' => $request->notes,
        ]);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('waste_images', 'public');
                $wasteRequest->images()->create(['image_path' => $path]);
            }
        }

        // Flash success message and redirect
        return redirect()
            ->route('waste-requests.index')
            ->with('success', 'Waste request submitted successfully!');
    }

    /**
     * Show single request details
     */
    public function show($id)
    {
        $data = WasteRequest::with('images')->findOrFail($id);
        return view('waste_requests.show', compact('data'));
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'collector_id' => 'required|exists:users,id',
        ]);

        $wasteRequest = WasteRequest::findOrFail($id);

        // Optional: check if selected user has 'collector' role
        $collector = \App\Models\User::findOrFail($request->collector_id);
        if (!$collector->hasRole('collector')) {
            return redirect()->back()->with('error', 'Selected user is not a collector.');
        }

        $wasteRequest->update([
            'assigned_to' => $request->collector_id,
            'status' => 'assigned',
        ]);

        return redirect()->route('waste-requests.show', $id)->with('success', 'Waste request assigned successfully!');
    }

}
