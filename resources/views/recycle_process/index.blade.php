@extends('website.master')

@section('title', 'Recycling Process')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recyclable Waste Requests</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="recycleProcessTable">
                        <thead class="table-light">
                            <tr>
                                <th>Waste ID</th>
                                <th>Citizen</th>
                                <th>Waste Type</th>
                                <th>Estimated Weight</th>
                                <th>Recycle Status</th>
                                <th>Action</th>
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
    $('#recycleProcessTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('recycle-process.index') }}",
        columns: [
            {data: 'waste_id', name: 'waste_id', orderable: false},
            {data: 'citizen', name: 'citizen', orderable: false},
            {data: 'waste_type', name: 'waste_type', orderable: false},
            {data: 'estimated_weight', name: 'estimated_weight', orderable: false},
            {data: 'recycle_status_badge', name: 'recycle_status_badge', orderable: false, className: 'text-center'},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
        ],
        order: [[0, 'desc']],
        pageLength: 10,
    });
});

function openCancelModal(id) {
    $('#cancelModal' + id).modal('show');
}



// Handle form submit via AJAX
$(document).on('submit', '.cancelForm', function(e){
    e.preventDefault();

    let form = $(this);
    let id = form.data('id');
    let url = '/recycle-process/cancel/' + id; // your route
    let data = form.serialize();

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function(response){
            $('#cancelModal' + id).modal('hide'); // close modal
            toastr.success(response.message);

            // Optional: reload DataTable row or table
            $('#recycleProcessTable').DataTable().ajax.reload();
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
