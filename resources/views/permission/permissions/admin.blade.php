@extends('website.master')

@section('header_css')
@endsection

@section('title')
    Manage Permission
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Page title -->
        <div class="row">
            <div class="col-12">
                {!! Session::has('success') ? '<div class="alert alert-success alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'. Session::get("success") .'</div>' : '' !!}
                {!! Session::has('error') ? '<div class="alert alert-danger alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'. Session::get("error") .'</div>' : '' !!}
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Manage Permission</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">User Role</li>
                            <li class="breadcrumb-item active">Permissions</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permission list table -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Permission List</h5>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->can('PERMISSION_ADD') || auth()->user()->can('MANAGE_PERMISSION'))
                            <button type="button" class="btn btn-success" onclick="showForm()">
                                <i class="fas fa-plus"></i> Add Permission
                            </button>
                        @endif
                        <table class="table table-bordered data-table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th width="3%">SL</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Permission Modal -->
        <div class="modal fade" id="ajaxModel" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title">Create Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="dataForm" autocomplete="off">
                        <div class="alert alert-danger" style="display:none"></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Permission Name" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('footer_js')
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // DataTable
    $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        iDisplayLength: 25,
        ajax: '{{ url("permissions/admin") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Show modal
    function showForm() {
        $('#dataForm').trigger("reset");
        $('#ajaxModel').modal('show');
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    // Save permission
    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#saveBtn').html('Saving...');
        $.ajax({
            data: $(this).serialize(),
            url: "{{ url('permissions/store') }}",
            type: "POST",
            dataType: 'json',
            success: function(result) {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                if(result.errors) {
                    $.each(result.errors, function(field, messages){
                        const inputField = $('#' + field);
                        inputField.addClass('is-invalid');
                        inputField.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                    });
                    $('#saveBtn').html('Save');
                } else {
                    $('#dataForm').trigger("reset");
                    $('#ajaxModel').modal('hide');
                    $('.data-table').DataTable().ajax.reload(null, false);
                    $('#saveBtn').html('Save');
                }
            },
            error: function(data) {
                console.log('Error:', data);
                $('#saveBtn').html('Save');
            }
        });
    });

    // Delete permission
    $('body').on('click', '.deleteData', function() {
        var data_id = $(this).data("id");
        if(confirm("Are you sure want to delete this permission?")) {
            $.ajax({
                type: "GET",
                url: "{{ url('permissions/delete') }}/" + data_id,
                success: function(data) {
                    $('.data-table').DataTable().ajax.reload(null, false);
                },
                error: function(data) {
                    console.log('Error:', data);
                }
            });
        }
    });
</script>
@endsection
