@extends('website.master')

@section('title', 'Waste Request Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Waste Request #{{ $data->id }}</h3>
                <p class="text-muted mb-0">{{ $data->pickup_date }} | {{ ucfirst($data->status) }}</p>
            </div>

            <div>
                <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i> Back
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Summary Card --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                {{-- Status Badge --}}
                <div class="mb-3">
                    <span class="badge 
                        @if($data->status == 'pending') bg-warning
                        @elseif($data->status == 'approved') bg-info
                        @elseif($data->status == 'assigned') bg-primary
                        @elseif($data->status == 'completed') bg-success
                        @else bg-secondary @endif
                        px-3 py-2 fs-6">
                        <i class="ri-checkbox-blank-circle-fill"></i> {{ ucfirst($data->status) }}
                    </span>
                </div>

                {{-- Quick Info --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">Citizen</small>
                            <h5 class="fw-semibold">{{ $data->user?->name ?? '-' }}</h5>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">City Corporation</small>
                            <h5 class="fw-semibold">{{ $data->cityCoporation?->name ?? '-' }}</h5>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">Ward</small>
                            <h5 class="fw-semibold">{{ $data->ward?->name ?? '-' }}</h5>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Detailed Information --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-body-tertiary">
                <h5 class="mb-0 fw-bold">Request Information</h5>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Zone Name:</strong>
                        <div class="text-muted">{{ $data->zone_name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Address:</strong>
                        <div class="text-muted">{{ $data->address }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Waste Type:</strong>
                        <div class="badge bg-dark px-3 py-2">{{ $data->waste_type }}</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Hazardous:</strong>
                        <div>
                            <span class="badge {{ $data->hazardous ? 'bg-danger' : 'bg-success' }}">
                                {{ $data->hazardous ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($data->waste_description)
                <div class="mb-3">
                    <strong>Description:</strong>
                    <div class="p-3 border rounded bg-light">{{ $data->waste_description }}</div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Estimated Weight:</strong>
                        <div>{{ $data->estimated_weight ?? '-' }} kg</div>
                    </div>
                    <div class="col-md-6">
                        <strong>Pickup Date:</strong>
                        <div>{{ $data->pickup_date }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Assigned Collector:</strong>
                    <div class="fw-semibold">
                        {{ $data->assignedCollector?->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Uploaded Images --}}
        @if($data->images && $data->images->count() > 0)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-body-tertiary">
                <h5 class="fw-bold mb-0">Uploaded Images</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($data->images as $image)
                        <div class="col-md-3">
                            <div class="border rounded overflow-hidden shadow-sm">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     class="w-100" style="height: 180px; object-fit: cover;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
