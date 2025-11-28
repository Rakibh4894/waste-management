@extends('website.master')

@section('title','My Dashboard')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <h4 class="mb-3">My Requests</h4>

    @php
      $cards = [
        ['label'=>'Total Requests','value'=>$total ?? 0,'sub'=>'All time'],
        ['label'=>'Pending','value'=>$summary['pending'] ?? 0,'sub'=>'Waiting approval'],
        ['label'=>'In Progress','value'=>(($summary['assigned'] ?? 0) + ($summary['in-progress'] ?? 0)) ?? 0,'sub'=>'Assigned'],
        ['label'=>'Completed','value'=>$summary['completed'] ?? 0,'sub'=>'Done'],
      ];
    @endphp

    @include('dashboard.partials._cards', ['cards'=>$cards])

    <div class="row">
      <div class="col-lg-7">
        <div class="card mb-3">
          <div class="card-header"><strong>Recent Requests</strong></div>
          <div class="card-body">
            <table class="table table-sm">
              <thead><tr><th>ID</th><th>Type</th><th>Pickup</th><th>Status</th><th></th></tr></thead>
              <tbody>
              @foreach($recentRequests as $r)
                <tr>
                  <td>#{{ $r['id'] }}</td>
                  <td class="text-capitalize">{{ $r['type'] }}</td>
                  <td>{{ $r['pickup_date'] }}</td>
                  <td><span class="badge {{ $r['status']=='pending' ? 'bg-warning' : ($r['status']=='completed' ? 'bg-success':'bg-info') }}">{{ ucfirst($r['status']) }}</span></td>
                  @can('MANAGE_WR')
                  <td>
                      <a class="btn btn-sm btn-outline-primary" 
                        href="{{ route('waste-requests.show', $r['id']) }}">
                          View
                      </a>
                  </td>
              @endcan
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card mb-3">
          <div class="card-header"><strong>Monthly Requests</strong></div>
          <div class="card-body">
            <canvas id="citizenMonthly" height="200"></canvas>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header"><strong>Quick Actions</strong></div>
          <div class="card-body">
            @can('WR_ADD')
            <a href="{{ route('waste-requests.create') }}" class="btn btn-primary w-100 mb-2">Create New Request</a>
            @endcan
            @can('MANAGE_WR')
            <a href="{{ route('waste-requests.index') }}" class="btn btn-outline-secondary w-100">View All My Requests</a>
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
  new Chart(document.getElementById('citizenMonthly'), { type:'bar', data:{ labels, datasets:[{ label:'Requests', data:vals }]}, options:{responsive:true} });
});
</script>
@endsection
