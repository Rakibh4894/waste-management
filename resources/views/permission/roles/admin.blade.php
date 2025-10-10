@extends('website.master')
@section('header_css')
<style>
    .single-search-input{
        width: 250px !important;
    }
</style>
@endsection
@section('title')
    Manage User Role
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">User Role</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active">User Role</li>
                                <li class="breadcrumb-item active">Role</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->


            <div class="row">
                <div class="col-lg-12">
                    {!! Session::has('success')
                   ? '<div class="alert alert-success alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' .
                       Session::get('success') .
                       '</div>'
                   : '' !!}
                    {!! Session::has('error')
                        ? '<div class="alert alert-danger alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' .
                            Session::get('error') .
                            '</div>'
                        : '' !!}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">View All User Role</h5>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-bordered display" style="width: 100%">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                    <tbody>

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end row-->


        </div>
        <!-- container-fluid -->

        <!--- Start Create Modal--->
        <div class="modal fade modal-lg" id="ajaxModel" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="exampleModalLabel">Create User Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                    </div>
                    <form id="dataForm" name="dataForm" class="tablelist-form" autocomplete="off">
                        <div class="alert alert-danger" style="display:none"></div>
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter User Role" value="" maxlength="" required>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <div class="hstack gap-2 justify-content-start">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                                <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--- End Create Model--->

        <!--- Start Update Model--->
        <div class="modal fade modal-lg" id="ajaxModelUpdate" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="exampleModalLabel">Edit User Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                    </div>
                    <form id="dataFormUpdate" name="dataFormUpdate" class="tablelist-form" autocomplete="off">
                        <div class="alert alert-danger" id="updateError" style="display:none"></div>
                        <div class="modal-body">
                            <input type="hidden" name="data_id" id="data_id">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="name2" name="name" placeholder="Enter User Role" maxlength="" value="" required>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <div class="hstack gap-2 justify-content-start">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" id="updateBtn">Update</button>
                                <!-- <button type="button" class="btn btn-success" id="edit-btn">Update</button> -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--- End Update Model--->


    </div>
@endsection



@section('footer_js')


    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var permission = @json(auth()->user()->can('0022'));
        $(function() {
            $('.table').DataTable({
                iDisplayLength: 25,
                processing: true,
                serverSide: true,
                searching: true,
                dom: '<"toolbar">Bfr<"topip"ip>t<"bottomip"ip>',
                ajax: {
                    url: "{{ url('/roles/list') }}",
                    method: 'get',
                    data: function(d) {
                        d._token = $('input[name="_token"]').val();
                    }
                },
                columns: [{
                    data: 'name',
                    name: 'name'
                },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                initComplete: function() {
                    this.api().columns([0]).every(function() {
                        var column = this;
                        var input = document.createElement("input");
                        input.classList.add("single-search-input");
                        $(input).appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? val : '', true, false).draw();
                            });
                    });

                    // if (userPermissions && userPermissions.includes('0000021')) {
                    //     $("div.toolbar").html("<a href='{{ url('roles/add') }}' class='pull-right btn btn-success btnAdd'> <i class='fas fa-plus'></i></a>");
                    // }

                    if (@json(auth()->user()->can('000250'))) {
                        $("div.toolbar").html(
                            // "<a href='{{ url('roles/add') }}' class='pull-right btn btn-success btnAdd'><i class='fas fa-plus'></i></a>"
                            "<a class='btn btn-success btnAdd' href='javascript:void(0)' onclick='showForm()'>  <i class='ri-add-fill'></i></a>"
                        );
                    }
                }
            });
        });

        // Start Delete Data
        $('body').on('click', '.deleteData', function() {

            var data_id = $(this).data("id");

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: true
            })

            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                confirmButtonColor: '#f63636',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    $.ajax({
                        type: "GET",
                        url: "{{ url('roles/delete') }}" + '/' + data_id,
                        success: function(data) {
                            toastr.success("Role Deleted!");
                            $('.dataTable').DataTable().ajax.reload(null, false);
                        },
                        error: function(data) {
                            console.log('Error:', data);
                        }
                    });
                }
            })
        });
        // End Delete Data

        function showForm() {
            // Clear all existing error messages
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $('label.error').remove();
            $('#dataForm').find('.error').removeClass('error');

            $('#data_id').val('');
            $('#dataForm').trigger("reset");
            $('#ajaxModel').modal('show');
        }

        $('body').on('click', '.editData', function() {
            var dataId = $(this).data('id');
            $.get("{{ url('roles/edit') }}" + '/' + dataId, function(data) {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('label.error').remove();
                $('#dataFormUpdate').find('.error').removeClass('error');

                $('#data_id').val(data.data.id);
                $('#ajaxModelUpdate').modal('show');
                $('.alert-danger').hide();

                $('#name2').val(data.data.name);
            })
        });

        $(document).ready(function () {
            $('#saveBtn').click(function (e) {
                $("#dataForm").validate({
                    rules: {
                        name: {
                            required: true,
                            maxlength: 30
                        }
                    },
                    messages: {
                        name: {
                            required: "Role name is required.",
                            maxlength: "Role cannot exceed 30 characters."
                        }
                    },
                    submitHandler: function (form) {
                        $('#saveBtn').html('Sending..');
                        $.ajax({
                            data: $('#dataForm').serialize(),
                            url: "{{ url('roles/permission-create') }}",
                            type: "POST",
                            dataType: 'json',
                            success: function(result) {
                                // Clear all existing error messages
                                $('.form-control').removeClass('is-invalid');
                                $('.invalid-feedback').remove();

                                if (result.errors) {
                                    $('#saveBtn').html('Save');
                                    $.each(result.errors, function (field, messages) {
                                        const inputField = $('#' + field);
                                        inputField.addClass('is-invalid');
                                        inputField.after('<div class="invalid-feedback">' + messages[0] + '</div>'); // Show the first error message
                                    });
                                } else {
                                    if (result.success) {
                                        $('#dataForm').trigger("reset");
                                        $('#ajaxModel').modal('hide');
                                        $('.dataTable').DataTable().ajax.reload(null, false);
                                        $('#saveBtn').html('Save');
                                        toastr.success(result.message);
                                    }
                                    else{
                                        toastr.error(result.message);
                                    }
                                }
                            },
                            error: function(data) {
                                toastr.error(data.responseJSON.message);
                                $('#saveBtn').html('Save');
                            }
                        });
                    }
                });
            });
            $('#updateBtn').click(function (e) {
                $("#dataFormUpdate").validate({
                    rules: {
                        name: {
                            required: true,
                            maxlength: 30
                        }
                    },
                    messages: {
                        name: {
                            required: "Role name is required.",
                            maxlength: "Role cannot exceed 30 characters."
                        }
                    },
                    submitHandler: function (form) {
                        // e.preventDefault();
                        $('#updateBtn').html('Updating..');
                        $.ajax({
                            data: $('#dataFormUpdate').serialize(),
                            url: "{{ url('roles/permission-update') }}",
                            type: "POST",
                            dataType: 'json',
                            success: function(result) {
                                // Clear all existing error messages
                                $('.form-control').removeClass('is-invalid');
                                $('.invalid-feedback').remove();

                                if (result.errors) {
                                    $('#updateBtn').html('Update');
                                    $.each(result.errors, function (field, messages) {
                                        const inputField = $('#' + field+'2');
                                        inputField.addClass('is-invalid');
                                        inputField.after('<div class="invalid-feedback">' + messages[0] + '</div>'); // Show the first error message
                                    });
                                } else {

                                    if (result.success) {
                                        $('#dataFormUpdate').trigger("reset");
                                        $('#ajaxModelUpdate').modal('hide');
                                        $('.dataTable').DataTable().ajax.reload(null, false);
                                        $('#updateBtn').html('Update');
                                        toastr.success(result.message);
                                    } else {
                                        toastr.error(result.message);
                                    }
                                }
                            },
                            error: function(data) {
                                console.log('Error:', data);
                                $('#saveBtn').html('Save');
                            }
                        });
                    }
                });
            });
        });
    </script>

@endsection
