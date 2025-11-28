@extends('website.master')

@section('title','Admin Dashboard')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <h4 class="mb-3">Admin Dashboard</h4>

    @php
      $cards = [
        ['label'=>'Total Requests','value'=>$summary['total'] ?? 0,'sub'=>'All time'],
        ['label'=>'Pending','value'=>$summary['pending'] ?? 0,'sub'=>'Waiting for approval'],
        ['label'=>'Assigned','value'=>$summary['assigned'] ?? 0,'sub'=>'Collector assigned'],
        ['label'=>'Completed','value'=>$summary['completed'] ?? 0,'sub'=>'Finished'],
      ];
    @endphp

    @include('dashboard.partials._cards', ['cards'=>$cards])

    <div class="row">
      <!-- Recent Requests -->
      <div class="col-lg-7">
        <div class="card mb-3">
          <div class="card-header">
            <strong>Recent Waste Requests</strong>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Citizen</th>
                  <th>Type</th>
                  <th>Pickup</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              @foreach($recentRequests as $r)
                <tr>
                  <td>#{{ $r->id }}</td>
                  <td>{{ $r->citizen->name ?? 'Unknown' }}</td>
                  <td class="text-capitalize">{{ $r->type }}</td>
                  <td>{{ $r->pickup_date }}</td>

                  <td>
                    <span class="badge 
                        {{ $r->status == 'pending' ? 'bg-warning' : 
                           ($r->status == 'assigned' ? 'bg-info' : 
                           ($r->status == 'completed' ? 'bg-success' : 'bg-secondary')) }}">
                      {{ ucfirst($r->status) }}
                    </span>
                  </td>

                  <td>
                    <div class="d-flex gap-1">
                      <!-- View -->
                      <a href="{{ route('waste-requests.show', $r->id) }}" 
                         class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                  </td>

                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Monthly Chart + Quick Actions -->
      <div class="col-lg-5">

        <div class="card mb-3">
          <div class="card-header"><strong>Monthly Requests</strong></div>
          <div class="card-body">
            <canvas id="adminMonthly" height="200"></canvas>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header"><strong>Quick Actions</strong></div>
          <div class="card-body">

            @can('WR_MANAGE')
            <a href="{{ route('waste-requests.index') }}" 
               class="btn btn-primary w-100 mb-2">Manage All Requests</a>
            @endcan  

            @can('WR_COLLECTOR')
            <a href="{{ route('waste-requests.index') }}" 
               class="btn btn-outline-secondary w-100 mb-2">Manage Collectors</a>
            @endcan

          </div>
        </div>

      </div>
    </div>

  </div>
</div>
@endsection


@section('footer_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const labels = {!! json_encode(array_keys($monthlyData)) !!};
  const vals = {!! json_encode(array_values($monthlyData)) !!};

  new Chart(document.getElementById('adminMonthly'), {
    type: 'bar',
    data: { 
      labels,
      datasets: [{
        label: 'Requests',
        data: vals
      }]
    },
    options: { responsive: true }
  });
});
</script>
@endsection
