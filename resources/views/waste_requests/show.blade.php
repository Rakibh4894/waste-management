@extends('website.master')

@section('title', 'Waste Request Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Waste Request Details</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('waste-requests.index') }}">Waste Requests</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Request #{{ $data->id }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Area:</strong> {{ $data->area }}</div>
                    <div class="col-md-6"><strong>Address:</strong> {{ $data->address }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Waste Type:</strong> {{ $data->waste_type }}</div>
                    <div class="col-md-6"><strong>Quantity:</strong> {{ $data->quantity ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Pickup Date:</strong> {{ $data->pickup_date }}</div>
                    <div class="col-md-6"><strong>Pickup Time:</strong> {{ $data->pickup_time }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($data->status) }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Description:</strong>
                        <p>{{ $data->description ?? '-' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Notes:</strong>
                        <p>{{ $data->notes ?? '-' }}</p>
                    </div>
                </div>

                @if($data->images && $data->images->count() > 0)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Uploaded Images:</strong>
                        </div>
                        @foreach($data->images as $image)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="card-img-top" alt="Waste Image">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                

                <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>

    </div>
</div>




@endsection
