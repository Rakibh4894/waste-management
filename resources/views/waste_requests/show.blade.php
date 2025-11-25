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
        <a href="{{ route('waste-requests.index') }}" class="btn btn-secondary">
          <i class="ri-arrow-left-line"></i> Back
        </a>
      </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
      {{-- Left: details + map + images --}}
      <div class="col-lg-8">

        {{-- Status + badges --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-body d-flex align-items-center justify-content-between">
            <div>
              <h5 class="mb-0">Request Overview</h5>
              <small class="text-muted">Requested by <strong>{{ $data->user?->name ?? '-' }}</strong></small>
            </div>
            <div>
              <span class="badge 
                @if($data->status == 'pending') bg-warning
                @elseif($data->status == 'approved') bg-info
                @elseif($data->status == 'assigned') bg-primary
                @elseif($data->status == 'completed') bg-success
                @elseif($data->status == 'in_progress') bg-secondary
                @else bg-secondary @endif
                px-3 py-2 fs-6 text-white">
                {{ ucfirst($data->status) }}
              </span>
            </div>
          </div>
        </div>

        {{-- Map card --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary">
            <strong>Location Preview</strong>
          </div>
          <div class="card-body p-0">
            <div id="map" style="height:360px; min-height:300px;"></div>
            <div class="p-3">
              <div><strong>Address:</strong> {{ $data->address }}</div>
              <div class="text-muted"><small>{{ $data->zone_name ? 'Zone: '.$data->zone_name : '' }}</small></div>
            </div>
          </div>
        </div>

        {{-- Request information --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Request Details</strong></div>
          <div class="card-body">
            <div class="row mb-2">
              <div class="col-md-6"><small class="text-muted">Waste Type</small><div class="fw-semibold">{{ $data->waste_type }}</div></div>
              <div class="col-md-6"><small class="text-muted">Estimated weight</small><div>{{ $data->estimated_weight ?? '-' }} kg</div></div>
            </div>

            <div class="row mb-2">
              <div class="col-md-6"><small class="text-muted">Hazardous</small>
                <div>
                  <span class="badge {{ $data->hazardous ? 'bg-danger' : 'bg-success' }}">
                    {{ $data->hazardous ? 'Yes' : 'No' }}
                  </span>
                </div>
              </div>
              <div class="col-md-6"><small class="text-muted">Pickup Date</small><div>{{ $data->pickup_date }}</div></div>
            </div>

            @if($data->waste_description)
            <div class="mt-3">
              <small class="text-muted">Description</small>
              <div class="p-3 border rounded bg-light">{{ $data->waste_description }}</div>
            </div>
            @endif
          </div>
        </div>

        {{-- Images --}}
        @if($data->images && $data->images->count() > 0)
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Uploaded Images</strong></div>
          <div class="card-body">
            <div class="row g-3">
              @foreach($data->images as $image)
                <div class="col-md-4 col-lg-3">
                  <div class="border rounded overflow-hidden">
                    <img src="{{ asset('storage/' . $image->image_path) }}" class="w-100" style="height:160px; object-fit:cover">
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif

      </div>

      {{-- Right: sidebar summary, collector performance, timeline, actions --}}
      <div class="col-lg-4">

        {{-- Sidebar summary --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Summary</strong></div>
          <div class="card-body">
            <div class="mb-2"><small class="text-muted">City Corporation</small><div class="fw-semibold">{{ $data->cityCorporation?->name ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Ward</small><div class="fw-semibold">{{ $data->ward?->name ?? '-' }}</div></div>
            <div class="mb-2"><small class="text-muted">Assigned Collector</small>
              <div class="d-flex justify-content-between align-items-center">
                <div class="fw-semibold" id="assignedCollectorName">{{ $data->assignedCollector?->name ?? '-' }}</div>
                @if($data->assignedCollector)
                  <button class="btn btn-sm btn-outline-primary" id="reassignBtn">Reassign</button>
                @else
                  <button class="btn btn-sm btn-primary" id="reassignBtn">Assign</button>
                @endif
              </div>
            </div>

            <div class="mb-2"><small class="text-muted">Status</small><div class="fw-semibold text-capitalize">{{ $data->status }}</div></div>
            <div class="mb-2"><small class="text-muted">Requested at</small><div class="text-muted">{{ $data->created_at }}</div></div>
          </div>
        </div>

        {{-- Collector performance --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Collector Performance</strong></div>
          <div class="card-body">
            <div id="collectorPerf">
              @if($data->assignedCollector)
                <div><small class="text-muted">In-progress requests</small>
                  <div class="h3 fw-bold" id="collectorInProgressCount">—</div>
                </div>
                <div class="mt-2"><small class="text-muted">Avg completion (placeholder)</small>
                  <div class="text-muted small">Data not available</div>
                </div>
              @else
                <div class="text-muted small">No collector assigned</div>
              @endif
            </div>
          </div>
        </div>

        {{-- Timeline --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Timeline</strong></div>
          <div class="card-body">
            <div class="timeline" style="display:flex; flex-direction:column; gap:12px;">
              @php
                $steps = [
                  'requested' => 'Requested',
                  'approved' => 'Approved',
                  'assigned' => 'Assigned',
                  'in_progress' => 'In Progress',
                  'completed' => 'Completed'
                ];
                $current = $data->status;
              @endphp

              @foreach($steps as $k => $label)
                <div class="d-flex align-items-center">
                  <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                    background: {{ $k === $current || (in_array($k, array_keys($steps)) && array_search($k,array_keys($steps)) < array_search($current, array_keys($steps)) ) ? '#0d6efd' : '#e9ecef' }};
                    color: {{ $k === $current || (in_array($k, array_keys($steps)) && array_search($k,array_keys($steps)) < array_search($current, array_keys($steps)) ) ? 'white' : '#6c757d' }};">
                    <i class="ri-checkbox-blank-circle-fill" style="font-size:10px;"></i>
                  </div>
                  <div class="ms-3">
                    <div class="{{ $k === $current ? 'fw-bold' : 'text-muted' }}">{{ $label }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Actions: change status quick buttons (requires permission 'update waste request') --}}
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-body-tertiary"><strong>Actions</strong></div>
          <div class="card-body">
            <div class="d-grid gap-2">
              @can('update waste request')
                <button class="btn btn-outline-primary btn-sm" data-status="in_progress" onclick="updateStatus(this)">Mark In Progress</button>
                <button class="btn btn-outline-success btn-sm" data-status="completed" onclick="updateStatus(this)">Mark Completed</button>
                <button class="btn btn-outline-danger btn-sm" data-status="cancelled" onclick="updateStatus(this)">Cancel Request</button>
              @endcan
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Reassign Modal --}}
<div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="reassignForm" method="POST" action="{{ route('waste-requests.assign', $data->id) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Assign / Reassign Collector</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Collector</label>
            <select id="collectorSelect" name="collector_id" class="form-select" required>
              <option value="">Loading collectors…</option>
            </select>
          </div>
          <div id="collectorLoadMsg" class="text-muted small">Collectors are loaded from server (all users with role Collector).</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('footer_js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

  const lat = @json($data->latitude);
  const lng = @json($data->longitude);
  const address = @json($data->address);
  const map = L.map('map').setView([23.8103, 90.4125], 12); // Default Dhaka view

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let marker;

  function setMarker(lat, lng, popupText = '') {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
    if (popupText) marker.bindPopup(popupText).openPopup();
    map.setView([lat, lng], 15);
  }

  if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
    setMarker(lat, lng, '{{ addslashes($data->address) }}');
  } else if (address) {
    // Geocode via Nominatim (fallback). Be kind to the API (one request).
    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address))
      .then(r => r.json())
      .then(res => {
        if (res && res.length > 0) {
          const first = res[0];
          setMarker(parseFloat(first.lat), parseFloat(first.lon), first.display_name);
        } else {
          // keep default view
        }
      })
      .catch(()=>{ /* ignore geocode errors */ });
  }

  // Collector in-progress count (if assigned)
  const assignedCollectorId = @json($data->assignedCollector?->id);
  if (assignedCollectorId) {
    fetch('/collector/in-progress-count/' + assignedCollectorId)
      .then(r => r.json())
      .then(json => {
        document.getElementById('collectorInProgressCount').innerText = json.count ?? '0';
        document.getElementById('assignedCollectorName').innerText = json.name ?? document.getElementById('assignedCollectorName').innerText;
      })
      .catch(()=>{ document.getElementById('collectorInProgressCount').innerText = '—'; });
  }

  // Reassign modal open -> load collectors via AJAX
  const reassignBtn = document.getElementById('reassignBtn');
  if (reassignBtn) {
    reassignBtn.addEventListener('click', function() {
      const modal = new bootstrap.Modal(document.getElementById('reassignModal'));
      loadCollectors().then(() => modal.show());
    });
  }

  async function loadCollectors() {
    const select = document.getElementById('collectorSelect');
    select.innerHTML = '<option value="">Loading...</option>';

    try {
      // NOTE: You should have a backend endpoint that returns collectors as JSON:
      // GET /collectors/list -> [{id:1, name:'Collector 1'}, ...]
      // If you prefer a different URL, update the fetch below accordingly.
      const res = await fetch('/collectors/list');
      if (!res.ok) throw new Error('No collectors endpoint');
      const data = await res.json();

      if (Array.isArray(data) && data.length) {
        select.innerHTML = '<option value="">-- Select Collector --</option>';
        data.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.id;
          opt.text = c.name;
          // preselect current assigned collector
          if (c.id == assignedCollectorId) opt.selected = true;
          select.appendChild(opt);
        });
      } else {
        select.innerHTML = '<option value="">No collectors found</option>';
      }
    } catch (err) {
      // fallback: try to fetch via /users?role=Collector (if you prefer)
      select.innerHTML = '<option value="">Unable to load collectors</option>';
      console.warn('loadCollectors error', err);
    }
  }

  // Submit reassign form via normal POST (route exists) — keep default behaviour,
  // but show loader and disable button to avoid double submits
  document.getElementById('reassignForm').addEventListener('submit', function(ev) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Assigning...';
  });

  // Status update via AJAX to your updateStatus route
  window.updateStatus = function(btn) {
    if (!confirm('Are you sure you want to change status to "' + btn.dataset.status + '"?')) return;
    const payload = new FormData();
    payload.append('_token', '{{ csrf_token() }}');
    payload.append('status', btn.dataset.status);

    fetch('{{ route('waste-requests.updateStatus', $data->id) }}', {
      method: 'POST',
      body: payload
    })
    .then(r => r.json())
    .then(json => {
      if (json.success) {
        // reload page to reflect changes (or update relevant parts dynamically)
        location.reload();
      } else {
        alert(json.message || 'Update failed');
      }
    })
    .catch(() => alert('Update failed'));
  };
});
</script>
@endsection
