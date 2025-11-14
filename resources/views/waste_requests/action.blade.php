@extends('website.master')

@section('content')
<div class="container">
    <h4>Update Request Status</h4>

    <form method="POST" action="{{ route('waste-requests.updateStatus', $wasteRequest->id) }}">
        @csrf

        <label>Status</label>
        <select class="form-select" name="status" required>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="assigned">Assigned</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <button class="btn btn-primary mt-3">Update</button>
    </form>
</div>
@endsection
