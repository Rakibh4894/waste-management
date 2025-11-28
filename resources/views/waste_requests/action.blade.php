@extends('website.master')

@section('title', 'Update Waste Request Status')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <h4>Update Request Status</h4>

        <form method="POST" action="{{ route('waste-requests.updateStatus', $wasteRequest->id) }}">
            @csrf

            <label>Status</label>
            <select class="form-select" name="status" required>
                @php
                    $currentStatus = $wasteRequest->status;

                    // Define allowed statuses per role
                    $statuses = [];

                    if (Auth::user()->hasRole('Super Admin')) {
                        $statuses = [
                            'pending' => 'Pending',
                            'assigned' => 'Assigned',
                            'in-progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ];
                    } elseif (Auth::user()->hasRole('collector')) {
                        // Collector can only change in-progress or completed if not cancelled
                        if ($currentStatus !== 'cancelled') {
                            $statuses = [
                                'in-progress' => 'In Progress',
                                'completed' => 'Completed',
                            ];
                        }
                    } elseif (Auth::user()->hasRole('admin')) {
                        $statuses = [
                            'assigned' => 'Assign',
                            'cancelled' => 'Cancel',
                        ];
                    }
                @endphp

                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ $currentStatus == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-primary mt-3">Update</button>
        </form>
    </div>
</div>
@endsection
