@extends('website.master')

@section('title', 'Manage Waste Requests')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
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

        {{-- Flash Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
            </div>
        @endif

        {{-- DataTable Card --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Waste Requests</h5>
                <a href="{{ route('waste-requests.create') }}" class="btn btn-success">
                    <i class="ri-add-fill"></i> Add New
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="wasteRequestTable" style="width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Citizen</th>
                                <th>Waste Type</th>
                                <th>Ward</th>
                                <th>Pickup Schedule</th>
                                <th>Status</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
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
    {data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center'},
    {data: 'citizen_name', name: 'citizen_name'},
    {data: 'waste_type', name: 'waste_type'},
    {data: 'ward_name', name: 'ward_name'},
    {data: 'pickup_schedule', name: 'pickup_schedule'},
    {data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-center'},
    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search waste requests..."
        }
    });
});
</script>
@endsection
