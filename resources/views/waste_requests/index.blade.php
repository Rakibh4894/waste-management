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

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <button class="btn btn-info" id="toggleAdvancedSearch">
                    <i class="ri-search-line"></i> Advanced Search
                </button>
            </div>
        </div>

        <div class="card mb-3" id="advancedSearchCard" style="display:none;">
            <div class="card-body">
                <form id="advancedSearchForm" class="row g-3">
                    <div class="col-md-2">
                        <label for="hazardous" class="form-label">Hazardous</label>
                        <select id="hazardous" name="hazardous" class="form-select">
                            <option value="">All</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="large_quantity" class="form-label">Large Quantity (> kg)</label>
                        <input type="number" class="form-control" id="large_quantity" name="large_quantity" placeholder="e.g., 50">
                    </div>

                    <div class="col-md-2">
                        <label for="long_pending" class="form-label">Long Pending (days)</label>
                        <input type="number" class="form-control" id="long_pending" name="long_pending" placeholder="e.g., 7">
                    </div>

                    <div class="col-md-2">
                        <label for="waste_type" class="form-label">Waste Type</label>
                        <select id="waste_type" name="waste_type" class="form-select">
                            <option value="">All</option>
                            <option value="household">Household</option>
                            <option value="plastic">Plastic</option>
                            <option value="medical">Medical</option>
                            <option value="construction">Construction</option>
                            <option value="organic">Organic</option>
                            <option value="industrial">Industrial</option>
                            <option value="e-waste">E-Waste</option>
                            <!-- add more types as needed -->
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select">
                            <option value="">All</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="submit" class="form-label">Action</label>
                        <div>
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="resetAdvancedSearch">Reset</button>
                        </div>
                        
                    </div>
                </form>
            </div>
        </div>


        {{-- DataTable Card --}}
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Waste Requests</h5>
                @can('WR_ADD')
                <a href="{{ route('waste-requests.create') }}" class="btn btn-success">
                    <i class="ri-add-fill"></i> Create Request
                </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="wasteRequestTable" style="width:100%;">
                        <thead class="table-light">
                            <tr>
                                <th>#ID</th>
                                <th>Citizen</th>
                                <th>Ward</th>
                                <th>City Corporation</th>
                                <th>Waste Type</th>
                                <th>Estimated Weight</th>
                                <th>Hazardous</th>
                                <th>Pickup Schedule</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Request Date</th>
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

    // Initialize DataTable and store in a variable
    var table = $('#wasteRequestTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('waste-requests.index') }}",
        columns: [
            {data: 'id', name: 'id', orderable: false},
            {data: 'citizen_name', name: 'citizen_name', orderable: false},
            {data: 'ward_name', name: 'ward_name', orderable: false},
            {data: 'city_corporation_name', name: 'city_corporation_name', orderable: false},
            {data: 'waste_type', name: 'waste_type', orderable: false},
            {data: 'estimated_weight', name: 'estimated_weight', orderable: false},
            {data: 'hazardous_badge', name: 'hazardous', orderable: false, className: 'text-center'},
            {data: 'pickup_schedule', name: 'pickup_schedule', orderable: false},
            {data: 'assigned_to_name', name: 'assigned_to_name', orderable: false},
            {data: 'status_badge', name: 'status_badge', orderable: false, className: 'text-center'},
            {data: 'request_date', name: 'request_date', orderable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search waste requests..."
        }
    });

    // Toggle Advanced Search
    $('#toggleAdvancedSearch').click(function(){
        $('#advancedSearchCard').slideToggle();
    });

    // Advanced Search submit
    $('#advancedSearchForm').on('submit', function(e){
        e.preventDefault();

        var params = $(this).serialize();
        table.ajax.url("{{ route('waste-requests.index') }}?" + params).load();
    });

    // Reset Advanced Search
    $('#resetAdvancedSearch').click(function(){
        $('#advancedSearchForm')[0].reset();
        table.ajax.url("{{ route('waste-requests.index') }}").load();
    });
});


function openCancelModal(id) {
    $('#cancelModal' + id).modal('show');
}

function openAssignModal(id) {
    $('#assignModal'+id).modal('show');
}

function openCompleteModal(id) {
    $('#completeModal'+id).modal('show');
}

// Handle form submit via AJAX
$(document).on('submit', '.cancelForm', function(e){
    e.preventDefault();

    let form = $(this);
    let id = form.data('id');
    let url = '/waste-requests/cancel/' + id; // your route
    let data = form.serialize();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response){
            $('#cancelModal' + id).modal('hide'); // close modal
            toastr.success(response.message);

            // Optional: reload DataTable row or table
            $('#wasteRequestTable').DataTable().ajax.reload();
        },
        error: function(xhr){
            let errors = xhr.responseJSON?.errors;
            let errorMessage = '';
            if(errors){
                $.each(errors, function(key, value){
                    errorMessage += value + '<br>';
                });
            } else {
                errorMessage = 'Something went wrong!';
            }
            toastr.error(errorMessage);
        }
    });
});

// Handle form submit via AJAX
$(document).on('submit', '.assignForm', function(e){
    e.preventDefault();

    let form = $(this);
    let id = form.data('id');
    let url = '/waste-requests/assign/' + id; // your route
    let data = form.serialize();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response){
            $('#assignModal' + id).modal('hide'); // close modal
            toastr.success(response.message);

            // Optional: reload DataTable row or table
            $('#wasteRequestTable').DataTable().ajax.reload();
        },
        error: function(xhr){
            let errors = xhr.responseJSON?.errors;
            let errorMessage = '';
            if(errors){
                $.each(errors, function(key, value){
                    errorMessage += value + '<br>';
                });
            } else {
                errorMessage = 'Something went wrong!';
            }
            toastr.error(errorMessage);
        }
    });
});


// Handle form submit via AJAX
$(document).on('submit', '.completeForm', function(e){
    e.preventDefault();

    let form = $(this);
    let id = form.data('id');
    let url = '/waste-requests/complete/' + id; // your route
    let data = form.serialize();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response){
            $('#completeModal' + id).modal('hide'); // close modal
            toastr.success(response.message);

            // Optional: reload DataTable row or table
            $('#wasteRequestTable').DataTable().ajax.reload();
        },
        error: function(xhr){
            let errors = xhr.responseJSON?.errors;
            let errorMessage = '';
            if(errors){
                $.each(errors, function(key, value){
                    errorMessage += value + '<br>';
                });
            } else {
                errorMessage = 'Something went wrong!';
            }
            toastr.error(errorMessage);
        }
    });
});

$(document).on('click', '.startTaskBtn', function(e){
    e.preventDefault();

    if(!confirm('Start this task?')) return;

    let form = $(this).closest('form');
    let url = form.attr('action');
    let data = form.serialize();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response){
            if(response.success){
                toastr.success(response.message);
                $('#wasteRequestTable').DataTable().ajax.reload(null, false); // reload table
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr){
            let errors = xhr.responseJSON?.errors;
            let errorMessage = '';
            if(errors){
                $.each(errors, function(key, value){
                    errorMessage += value + '<br>';
                });
            } else {
                errorMessage = 'Something went wrong!';
            }
            toastr.error(errorMessage);
        }
    });
});
</script>



@endsection
