@extends('website.master')

@section('title', 'Add Waste Request')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
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

        {{-- Form Card --}}
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="card-title mb-0">Create Waste Request</h5></div>

            <div class="card-body">
                <form method="POST" action="{{ route('waste-requests.store') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Waste Type --}}
                    <div class="mb-3">
                        <label class="form-label">Waste Type <span class="text-danger">*</span></label>
                        <select name="waste_type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="household">Household</option>
                            <option value="plastic">Plastic</option>
                            <option value="medical">Medical</option>
                            <option value="construction">Construction</option>
                            <option value="organic">Organic</option>
                            <option value="industrial">Industrial</option>
                            <option value="e-waste">E-Waste</option>
                        </select>
                        @error('waste_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Region --}}
                    <div class="mb-3">
                        <label class="form-label">Region / Zone <span class="text-danger">*</span></label>
                        <select name="region_id" class="form-select" required>
                            <option value="">-- Select Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->number }}</option>
                            @endforeach
                        </select>
                        @error('region_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Optional Zone Name --}}
                    <div class="mb-3">
                        <label class="form-label">Zone / Area Name (Optional)</label>
                        <input type="text" name="zone_name" value="{{ old('zone_name') }}" class="form-control" placeholder="e.g., Mirpur-2">
                    </div>

                    {{-- Address --}}
                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control" required placeholder="House #, Road #, Area">
                        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Map (Leaflet Integration) --}}
                    <div class="mb-3">
                        <label class="form-label">Select Location on Map</label>
                        <div id="map" style="height: 300px; border-radius: 10px; border: 1px solid #ddd;"></div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Estimated Weight --}}
                    <div class="mb-3">
                        <label class="form-label">Estimated Weight (kg)</label>
                        <input type="number" step="0.01" name="estimated_weight" value="{{ old('estimated_weight') }}" class="form-control" placeholder="e.g., 5.50">
                    </div>

                    {{-- Hazardous --}}
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="hazardous" name="hazardous" value="1">
                        <label class="form-check-label" for="hazardous">Is this waste hazardous?</label>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Waste Description</label>
                        <textarea name="waste_description" class="form-control" rows="3" placeholder="Provide a short description...">{{ old('waste_description') }}</textarea>
                    </div>

                    {{-- Pickup Date --}}
                    <div class="mb-3">
                        <label class="form-label">Preferred Pickup Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="pickup_date" class="form-control" required>
                        @error('pickup_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Upload Images --}}
                    <div class="mb-3">
                        <label class="form-label">Upload Images (optional)</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">You can upload multiple images (max 2MB each)</small>
                    </div>

                    {{-- Submit --}}
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                        <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('footer_js')
{{-- Leaflet Map Integration --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const defaultLat = 23.8103; // Dhaka default
    const defaultLng = 90.4125;

    const map = L.map('map').setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker;

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    });
});
</script>
@endsection
