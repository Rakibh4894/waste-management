@extends('website.master')

@section('title', 'Waste Request Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
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

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Request #{{ $data->id }}</h5>
            </div>
            <div class="card-body">

                {{-- Basic Info --}}
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Citizen:</strong> {{ $data->user?->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Region:</strong> {{ $data->ward?->name ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Zone Name:</strong> {{ $data->zone_name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Address:</strong> {{ $data->address }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Waste Type:</strong> {{ $data->waste_type }}</div>
                    <div class="col-md-6"><strong>Description:</strong> {{ $data->waste_description ?? '-' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Estimated Weight:</strong> {{ $data->estimated_weight ?? '-' }} kg</div>
                    <div class="col-md-6"><strong>Hazardous:</strong> {{ $data->hazardous ? 'Yes' : 'No' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Pickup Date:</strong> {{ $data->pickup_date }}</div>
                    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($data->status) }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6"><strong>Assigned Collector:</strong> {{ $data->assignedEmployee?->name ?? '-' }}</div>
                </div>

                {{-- Uploaded Images --}}
                @if($data->images && $data->images->count() > 0)
                    <div class="row mb-3">
                        <div class="col-12 mb-2"><strong>Uploaded Images:</strong></div>
                        @foreach($data->images as $image)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="card-img-top" alt="Waste Image">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Back Button --}}
                <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary mt-3">Back to List</a>

            </div>
        </div>

    </div>
</div>
@endsection
