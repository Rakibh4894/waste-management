@extends('website.master')

@section('title','Collector Dashboard')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <h4 class="mb-3">Collector Dashboard</h4>

    @php
      $cards = [
        ['label'=>"Today's Tasks",'value'=>count($todayTasks),'sub'=>'Assigned to you today','icon'=>'<i class="ri-calendar-check-line fs-3 text-primary"></i>'],
        ['label'=>'Upcoming','value'=>count($upcomingTasks),'sub'=>'Next 7 days','icon'=>'<i class="ri-calendar-event-line fs-3 text-info"></i>'],
        ['label'=>'Completed','value'=>$completedCount,'sub'=>'Total completed','icon'=>'<i class="ri-check-line fs-3 text-success"></i>'],
        ['label'=>'Hazardous Assigned','value'=>rand(0,4),'sub'=>'Handle carefully','icon'=>'<i class="ri-alert-line fs-3 text-danger"></i>'],
      ];
    @endphp

    @include('dashboard.partials._cards', ['cards'=>$cards])

    <div class="row">
      <div class="col-lg-7">
        <div class="card mb-3">
          <div class="card-header"><strong>Today's Route</strong></div>
          <div class="card-body p-0">
            <div id="collectorMap" style="height:380px"></div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card mb-3">
          <div class="card-header"><strong>Assigned Tasks</strong></div>
          <div class="card-body">
            @foreach($upcomingTasks as $task)
              <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between">
                  <div><strong>#{{ $task['id'] }} — {{ $task['citizen'] }}</strong><br><small class="text-muted">{{ $task['address'] }}</small></div>
                  <div class="text-end">
                    <small class="text-muted">{{ \Carbon\Carbon::parse($task['pickup_date'])->format('d M H:i') }}</small><br>
                    <button class="btn btn-sm btn-success mt-2" onclick="markCollected({{ $task['id'] }})">Mark Collected</button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection

@section('footer_js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const tasks = {!! json_encode($upcomingTasks) !!};
  const map = L.map('collectorMap').setView([23.8103,90.4125], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

  tasks.forEach(t => {
    if (t.lat && t.lng) {
      L.marker([t.lat, t.lng]).addTo(map).bindPopup(`<strong>#${t.id}</strong><br>${t.citizen}<br>${t.address}`);
    }
  });

  window.markCollected = function(id){
    if (!confirm('Mark request #'+id+' as collected?')) return;
    // TODO: call your API to mark as collected via fetch POST
    alert('Marked as collected (demo): #' + id);
  };
});
</script>
@endsection
