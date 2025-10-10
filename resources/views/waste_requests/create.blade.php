@extends('website.master')

@section('title', 'Add Waste Request')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
            <h4 class="mb-sm-0">Add Waste Request</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('waste-requests.index') }}">Waste Requests</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Create Waste Request</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('waste-requests.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Area *</label>
                        <input type="text" name="area" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waste Type *</label>
                        <select name="waste_type" class="form-select" required>
                            <option>Household</option>
                            <option>Plastic</option>
                            <option>Medical</option>
                            <option>Construction</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pickup Date *</label>
                        <input type="date" name="pickup_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pickup Time *</label>
                        <select name="pickup_time" class="form-select" required>
                            <option>Morning</option>
                            <option>Afternoon</option>
                            <option>Evening</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Images</label>
                        <input type="file" name="images[]" class="form-control" multiple>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
