@extends('website.master')

@section('title', 'Manage Waste Requests')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Waste Requests</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Waste Requests</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Waste Requests</h5>
                <a href="{{ route('waste-requests.create') }}" class="btn btn-success">
                    <i class="ri-add-fill"></i> Add New
                </a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="wasteRequestTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Waste Type</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('footer_js')
<script>
$(function() {
    $('#wasteRequestTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('waste-requests.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'waste_type', name: 'waste_type'},
            {data: 'pickup_schedule', name: 'pickup_schedule'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
});
</script>
@endsection
