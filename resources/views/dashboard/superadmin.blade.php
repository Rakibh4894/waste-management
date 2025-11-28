@extends('website.master')

@section('title','Super Admin Dashboard')

@section('content')
<div class="page-content">
  <div class="container-fluid">

    <h4 class="mb-3">Super Admin Dashboard</h4>

    @php
      $cards = [
        ['label'=>'Total Requests','value'=>$totalRequests ?? 0,'sub'=>'All time','icon'=> '<i class="ri-stack-line fs-3 text-primary"></i>'],
        ['label'=>'Total Users','value'=>$totalUsers ?? 0,'sub'=>'Citizens + Admins + Collectors','icon'=>'<i class="ri-user-3-line fs-3 text-info"></i>'],
        ['label'=>'Collectors','value'=>$collectorsCount ?? 0,'sub'=>'Active collectors','icon'=>'<i class="ri-truck-line fs-3 text-success"></i>'],
        ['label'=>'Hazardous Pending','value'=>rand(5,25),'sub'=>'Requires attention','icon'=>'<i class="ri-alert-line fs-3 text-danger"></i>'],
      ];
    @endphp

    @include('dashboard.partials._cards', ['cards'=>$cards])

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-3">
          <div class="card-header"><strong>Monthly Requests (Last 12 months)</strong></div>
          <div class="card-body">
            <canvas id="superMonthlyChart" height="120"></canvas>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header"><strong>City Comparison</strong></div>
          <div class="card-body">
            <canvas id="citiesChart" height="120"></canvas>
          </div>
        </div>

      </div>

      <div class="col-lg-4">
        <div class="card mb-3">
          <div class="card-header"><strong>Waste Type Distribution</strong></div>
          <div class="card-body">
            <canvas id="typePieChart" height="200"></canvas>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header"><strong>Recent Activity</strong></div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li>New request #{{rand(900,999)}} created</li>
              <li>Collector John assigned to #{{rand(800,899)}}</li>
              <li>New Collector registered</li>
              <li>System backups completed</li>
            </ul>
          </div>
        </div>
      </div>

    </div>

    <div class="card mb-3">
      <div class="card-header"><strong>Requests Map (sample markers)</strong></div>
      <div class="card-body p-0">
        <div id="superMap" style="height:380px;"></div>
      </div>
    </div>

  </div>
</div>
@endsection

@section('footer_js')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){

  // Monthly chart data from controller
  const monthlyLabels = {!! json_encode(array_keys($monthlyData)) !!};
  const monthlyValues = {!! json_encode(array_values($monthlyData)) !!};

  const ctx = document.getElementById('superMonthlyChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: { labels: monthlyLabels, datasets: [{ label: 'Requests', data: monthlyValues, fill: true, tension: 0.3 }] },
    options: { responsive:true, plugins:{legend:{display:false}} }
  });

  // Pie chart for types
  const typeLabels = {!! json_encode(array_keys($typeDistribution)) !!};
  const typeValues = {!! json_encode(array_values($typeDistribution)) !!};
  const pieCtx = document.getElementById('typePieChart').getContext('2d');
  new Chart(pieCtx, { type:'pie', data:{ labels:typeLabels, datasets:[{ data:typeValues }]}, options:{responsive:true} });

  // Cities bar chart
  const cityLabels = {!! json_encode(array_column($cities,'name')) !!};
  const cityTotals = {!! json_encode(array_column($cities,'total')) !!};
  const citiesCtx = document.getElementById('citiesChart').getContext('2d');
  new Chart(citiesCtx, { type:'bar', data:{ labels:cityLabels, datasets:[{ label:'Total', data:cityTotals }]}, options:{responsive:true} });

  // Leaflet map with sample markers
  const map = L.map('superMap').setView([23.8103,90.4125], 7);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

  // sample markers (replace with real coordinates)
  const samplePoints = [
    [23.8103,90.4125,'Dhaka - Pending'],
    [22.3569,91.7832,'Chittagong - Completed'],
    [24.3636,88.6241,'Rangpur - Hazardous'],
  ];
  samplePoints.forEach(p => {
    L.marker([p[0],p[1]]).addTo(map).bindPopup(p[2]);
  });

});
</script>
@endsection
