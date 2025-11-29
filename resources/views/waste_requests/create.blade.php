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
            
                    <div class="row">
                        {{-- Waste Type --}}
                        <div class="col-md-6 mb-3">
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

                        {{-- City Corporation --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City Corporation <span class="text-danger">*</span></label>
                            <select name="city_corporation_id" id="city_corporation_id" class="form-select" required>
                                <option value="">-- Select City Corporation --</option>
                                @foreach($cityCorporations as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->title }}</option>
                                @endforeach
                            </select>
                            @error('city_corporation_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Ward --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ward <span class="text-danger">*</span></label>
                            <select name="ward_id" id="ward_id" class="form-select" required>
                                <option value="">-- Select Ward --</option>
                            </select>
                            @error('ward_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Zone / Area Name --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Zone / Area Name (Optional)</label>
                            <input type="text" name="zone_name" value="{{ old('zone_name') }}" class="form-control" placeholder="e.g., Mirpur-2">
                        </div>

                        {{-- Address --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" name="address" value="{{ old('address') }}" class="form-control" required placeholder="House #, Road #, Area">
                            @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Estimated Weight --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimated Weight (kg)</label>
                            <input type="number" step="0.01" name="estimated_weight" value="{{ old('estimated_weight') }}" class="form-control" placeholder="e.g., 5.50">
                        </div>

                        {{-- Hazardous & Priority same row --}}
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch w-50">
                                <input type="checkbox" class="form-check-input" id="hazardous" name="hazardous" value="1">
                                <label class="form-check-label" for="hazardous">Is this waste hazardous?</label>
                            </div>

                            <div class="ms-4 w-50">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pickup Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preferred Pickup Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="pickup_date" class="form-control" required>
                            @error('pickup_date') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Waste Description --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Waste Description</label>
                            <textarea name="waste_description" class="form-control" rows="3" placeholder="Provide a short description...">{{ old('waste_description') }}</textarea>
                        </div>

                        {{-- Map --}}
                        <div class="col-md-12 mb-3">
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

                        {{-- Upload Images --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Upload Images (multiple)</label>
                            <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">

                            <div id="preview-container" class="mt-3 d-flex flex-wrap gap-3"></div>

                            <small class="text-muted">You can upload multiple images (max 2MB each)</small>
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                            <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Dependent Dropdown: Load Wards When Selecting City Corp
    document.getElementById('city_corporation_id').addEventListener('change', function () {

        let cityCorpId = this.value;
        let wardSelect = document.getElementById('ward_id');

        wardSelect.innerHTML = '<option value="">Loading...</option>';

        if (!cityCorpId) {
            wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
            return;
        }

        fetch('/get-wards/' + cityCorpId)
            .then(response => response.json())
            .then(data => {
                wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
                data.forEach(ward => {
                    wardSelect.innerHTML += `<option value="${ward.id}">${ward.number}</option>`;
                });
            })
            .catch(error => {
                wardSelect.innerHTML = '<option value="">Error loading wards</option>';
            });
    });

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Multiple Images Preview
    let imageInput = document.getElementById("images");
    let previewContainer = document.getElementById("preview-container");

    imageInput.addEventListener("change", function() {
        previewContainer.innerHTML = ""; // Clear old previews

        [...this.files].forEach((file, index) => {

            if (!file.type.startsWith("image/")) return;

            let reader = new FileReader();
            reader.onload = function(e) {

                let previewBox = document.createElement("div");
                previewBox.style.position = "relative";
                previewBox.style.width = "120px";

                previewBox.innerHTML = `
                    <img src="${e.target.result}" 
                         class="img-thumbnail" 
                         style="height:100px; width:100%; object-fit:cover; border-radius:8px;">

                    <button type="button" 
                            class="btn btn-sm btn-danger remove-img" 
                            style="position:absolute; top:5px; right:5px; padding:2px 5px;">
                        X
                    </button>
                `;

                previewContainer.appendChild(previewBox);

                // Remove button action
                previewBox.querySelector(".remove-img").addEventListener("click", () => {
                    previewBox.remove();

                    // Remove from input file list
                    let dt = new DataTransfer();
                    let inputFiles = imageInput.files;

                    for (let i = 0; i < inputFiles.length; i++) {
                        if (i !== index) dt.items.add(inputFiles[i]);
                    }

                    imageInput.files = dt.files;
                });

            };

            reader.readAsDataURL(file);
        });
    });

});
</script>
@endsection
