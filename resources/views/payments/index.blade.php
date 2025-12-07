@extends('website.master')

@section('title', 'Manage Payments')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Monthly Payments</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Payments</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div>
        @endif

        {{-- Payments Table --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Payments</h5>
                @can('WR_ADD')
                <a href="{{ route('payments.create') }}" class="btn btn-success">
                    <i class="ri-add-fill"></i> Create Payment
                </a>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="billTable" style="width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th>#ID</th>
                                <th>Citizen Name</th>
                                <th>City Corporation</th>
                                <th>Ward</th>
                                <th>Bill Month</th>
                                <th>Amount</th>
                                <th>Created At</th>
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

    var table = $('#billTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('payments.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'citizen_name', name: 'citizen_name'},
            {data: 'city_corporation_name', name: 'city_corporation_name', orderable: false},
            {data: 'ward_name', name: 'ward_name'},
            {data: 'payment_month', name: 'payment_month'},
            {data: 'amount', name: 'amount'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
        ],
        order: [[0, 'desc']],
        pageLength: 10
    });

});
</script>
@endsection
