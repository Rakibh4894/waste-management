@extends('website.master')

@section('title', 'Waste Request Details')

@section('content')
<div class="page-content">
  <div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold mb-1">Waste Request #{{ $data->id }}</h3>
        <p class="text-muted mb-0">{{ $data->pickup_date }} | <span class="text-capitalize">{{ $data->status }}</span></p>
      </div>
      <div>
        <a href="{{ route('waste-requests.index') }}" class="btn btn-outline-secondary">
          <i class="ri-arrow-left-line"></i> Back
        </a>
      </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="row g-4">
      {{-- Left: Details, Map, Images --}}
      <div class="col-lg-8">

        {{-- Request Overview --}}
        <div class="card mb-3 shadow-sm border-primary">
          <div class="card-body d-flex justify-content-between align-items-center">
            <div>
              <h5 class="fw-bold mb-1">Request Overview</h5>
              <small class="text-muted">Requested by <strong>{{ $data->user?->name ?? '-' }}</strong></small>
            </div>
            <div class="text-end">
              <span class="badge fs-6 px-3 py-2 text-white
                @switch($data->status)
                  @case('pending') bg-warning
                  @break
                  @case('assigned') bg-primary
                  @break
                  @case('in_progress') bg-secondary
                  @break
                  @case('completed') bg-success
                  @break
                  @default bg-dark
                @endswitch
              ">
                {{ ucfirst($data->status) }}
              </span>

              @if($data->status == "completed" && $data->complete_remarks != "")
              <br>
              <small class="text-muted"><i>{{ $data->complete_remarks }}</i></small>
              @endif

              @if($data->status == "cancelled" && $data->cancel_reason != "")
              <br>
              <small class="text-muted"><i>{{ $data->cancel_reason }}</i></small>
              @endif

            </div>
          </div>
        </div>

        {{-- Location Map --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Location Preview</strong></div>
          <div class="card-body p-0">
            <div id="map" style="height:360px; min-height:300px;"></div>
            <div class="p-3 bg-light rounded-bottom">
              <div><strong>Address:</strong> {{ $data->address }}</div>
              @if($data->zone_name)
                <div class="text-muted"><small>Zone: {{ $data->zone_name }}</small></div>
              @endif
            </div>
          </div>
        </div>

        {{-- Request Details --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Request Details</strong></div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <small class="text-muted">Waste Type</small>
                <div class="fw-semibold">{{ ucfirst($data->waste_type) }}</div>
              </div>
              <div class="col-md-6">
                <small class="text-muted">Estimated Weight</small>
                <div>{{ $data->estimated_weight ?? '-' }} kg</div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <small class="text-muted">Hazardous</small>
                <div>
                  <span class="badge {{ $data->hazardous ? 'bg-danger' : 'bg-success' }}">
                    {{ $data->hazardous ? 'Yes' : 'No' }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <small class="text-muted">Recyclable</small>
                <div>
                  <span class="badge {{ $data->is_recyclable ? 'bg-success' : 'bg-secondary' }}">
                    {{ $data->is_recyclable ? 'Yes' : 'No' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <small class="text-muted">Priority</small>
                <div>
                  <span class="badge 
                    @switch($data->priority)
                      @case('high') bg-danger
                      @break
                      @case('urgent') bg-dark
                      @break
                      @case('low') bg-warning text-dark
                      @break
                      @default bg-primary
                    @endswitch
                  ">
                    {{ ucfirst($data->priority) }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <small class="text-muted">Pickup Date</small>
                <div>{{ $data->pickup_date }}</div>
              </div>
            </div>

            @if($data->waste_description)
            <div class="mt-3">
              <small class="text-muted">Description</small>
              <div class="p-3 border rounded bg-light">{{ $data->waste_description }}</div>
            </div>
            @endif
          </div>
        </div>

        {{-- Uploaded Images --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Uploaded Images</strong></div>
          <div class="card-body">
            <div class="row g-3">
              @foreach($data->images as $image)
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="gallery-thumb border rounded overflow-hidden shadow-sm cursor-pointer" onclick="openImageModal('{{ asset($image->image_path) }}')">
                    <img src="{{ asset($image->image_path) }}" class="img-fluid" style="height:150px; width:100%; object-fit:cover;">
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

      </div>

      {{-- Right Sidebar --}}
      <div class="col-lg-4">

        {{-- Summary --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Summary</strong></div>
          <div class="card-body">
            <div class="mb-2"><small class="text-muted">City Corporation</small><div class="fw-semibold">{{ $data->cityCorporation?->title ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Ward</small><div class="fw-semibold">{{ $data->ward?->number ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Assigned Collector</small>
              <div class="fw-semibold" id="assignedCollectorName">{{ $data->assignedCollector?->name ?? '-' }}</div>
            </div>
            <div class="mb-2"><small class="text-muted">Status</small><div class="fw-semibold text-capitalize">{{ $data->status }}</div></div>
            <div class="mb-2"><small class="text-muted">Requested at</small><div class="text-muted">{{ $data->created_at }}</div></div>
          </div>
        </div>

        {{-- Collector Performance --}}
        @if($data->assignedCollector)
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Collector Performance</strong></div>
          <div class="card-body">
            <div class="mb-2"><small class="text-muted">In-progress requests</small>
              <div class="h3 fw-bold" id="collectorInProgressCount">—</div>
            </div>
            <div><small class="text-muted">Average completion (placeholder)</small>
              <div class="text-muted small">Data not available</div>
            </div>
          </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-primary text-white"><strong>Timeline</strong></div>
          <div class="card-body">
            <div class="timeline d-flex flex-column gap-3">
              @php
                $steps = [
                  'requested' => 'Requested',
                  'assigned' => 'Assigned',
                  'in_progress' => 'In Progress',
                  'completed' => 'Completed'
                ];
                $current = $data->status;
              @endphp
              @foreach($steps as $k => $label)
              <div class="d-flex align-items-center">
                <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                  background: {{ $k === $current || array_search($k,array_keys($steps)) <= array_search($current, array_keys($steps)) ? '#0d6efd' : '#e9ecef' }};
                  color: {{ $k === $current || array_search($k,array_keys($steps)) <= array_search($current, array_keys($steps)) ? '#fff' : '#6c757d' }};">
                  <i class="ri-checkbox-blank-circle-fill" style="font-size:10px;"></i>
                </div>
                <div class="ms-3 fw-semibold {{ $k === $current ? 'text-primary' : 'text-muted' }}">{{ $label }}</div>
              </div>
              @endforeach
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark p-0">
      <img id="previewImg" src="" class="w-100 rounded">
    </div>
  </div>
</div>
@endsection

@section('footer_js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Map
  const lat = @json($data->latitude);
  const lng = @json($data->longitude);
  const map = L.map('map').setView([23.8103, 90.4125], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

  let marker;
  if(lat && lng){ marker = L.marker([lat, lng]).addTo(map).bindPopup('{{ addslashes($data->address) }}').openPopup(); map.setView([lat, lng],15); }

  // Collector in-progress count
  const assignedCollectorId = @json($data->assignedCollector?->id);
  if(assignedCollectorId){
    fetch('/collector/in-progress-count/' + assignedCollectorId).then(r=>r.json()).then(json=>{
      document.getElementById('collectorInProgressCount').innerText = json.count ?? '0';
      document.getElementById('assignedCollectorName').innerText = json.name ?? document.getElementById('assignedCollectorName').innerText;
    });
  }

});

// Image modal
function openImageModal(src){
  document.getElementById('previewImg').src = src;
  new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection
