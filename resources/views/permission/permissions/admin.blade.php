@extends('website.master')
@section('header_css')

@endsection
@section('title')
    Manage Permission
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    {!! Session::has('success') ? '<div class="alert alert-success alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'. Session::get("success") .'</div>' : '' !!}
                    {!! Session::has('error') ? '<div class="alert alert-danger alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'. Session::get("error") .'</div>' : '' !!}
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Manage Permission</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active">User Role</li>
                                <li class="breadcrumb-item active">Permissions</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->


            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Permission List</h5>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-bordered data-table" style="width: 100%">

                                    <thead>
                                    <tr>

                                        <th width="3%">SL</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Module</th>
                                        <th>Menu</th>
                                        <th>Sub Menu</th>
                                        <th>Description</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>

                                        <th width="3%"></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
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
        <div class="modal fade modal-lg" id="ajaxModel" tabindex="-1" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="exampleModalLabel">Create Permission</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                id="close-modal"></button>
                    </div>
                    <form id="dataForm" name="dataForm" class="tablelist-form" autocomplete="off">
                        <div class="alert alert-danger" style="display:none"></div>
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="code" class="form-label">Code<span
                                        style="color: red;">*</span> </label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="Enter code" value="" maxlength="6" required>
                            </div>

                            <div class="mb-3">
                                <label for="module" class="form-label">Module <span style="color: red;">*</span></label>

                                <select class="form-control" name="module_id" id="module_id" required>
                                    @foreach ($modules as $module)
                                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="menu" class="form-label">Menu <span style="color: red;">*</span></label>
                                <select class="form-control" name="menu_id" id="menu_id" required>

                                </select>

                            </div>

                            <div class="mb-3">
                                <label for="sub_menu" class="form-label">Sub Menu <span style="color: red;">*</span></label>
                                <select class="form-control" name="sub_menu_id" id="sub_menu_id" required>

                                </select>

                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="" required>

                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span style="color: red;"></span></label>
                                <input type="text" class="form-control" id="description" name="description" placeholder="Enter Description" value="">

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



    </div>
@endsection



@section('footer_js')


    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Start View Data
        $('.table').DataTable({
            language: {
                paginate: {
                    next: '&#8594;', // or '→'
                    previous: '&#8592;' // or '←'
                }
            },
            processing: true,
            serverSide: true,
            iDisplayLength: 25,
            aaSorting: [
                ['0', 'desc']
            ],
            dom: '<"toolbar">Bfr<"topip"ip>t<"bottomip"ip>',

            ajax: '{{ url("permissions/admin") }}',
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'module_id',
                    name: 'module_id'
                },
                {
                    data: 'menu_id',
                    name: 'menu_id'
                },
                {
                    data: 'sub_menu_id',
                    name: 'sub_menu_id'
                },
                {
                    data: 'description',
                    name: 'description'
                },

            ],
            initComplete: function () {
                this.api().columns([1, 2, 3, 4, 5, 6]).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    input.classList.add("single-search-input");
                    $(input).appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? val : '', true, false).draw();
                        });
                });

                if (@json(auth()->user()->can('000254'))) {
                    $("div.toolbar").html(
                        "<a class='btn btn-success btnAdd' href='javascript:void(0)' onclick='showForm()'> <i class='fas fa-plus'></i></a>"
                    );
                }
            }
        });

        // Start Create Data
        function showForm() {
            // Clear all existing error messages
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $('label.error').remove();
            $('#dataForm').find('.error').removeClass('error');

            $('#data_id').val('');
            $('#dataForm').trigger("reset");
            $('#ajaxModel').modal('show');
            $('#module_id, #menu_id, #sub_menu_id').val(null).trigger('change'); // Reset the Select2 values
            $('#menu_id, #sub_menu_id').html(''); // Reset the Select2 values
        }

        $(document).ready(function () {
            $('#saveBtn').click(function (e) {
                $("#dataForm").validate({
                    rules: {
                        code: {
                            required: true,
                            maxlength: 7
                        },
                        name: {
                            required: true,
                            maxlength: 50
                        },
                        module_id: {
                            required: true,
                        },
                        menu_id: {
                            required: true,
                        },
                        sub_menu_id: {
                            required: true,
                        }
                    },
                    messages: {
                        code: {
                            required: "Code is required.",
                            maxlength: "Code must not exceed 7 characters."
                        },
                        name: {
                            required: "Name is required.",
                            maxlength: "Name must not exceed 50 characters."
                        },
                        module_id: {
                            required: "Module is required.",
                        },
                        menu_id: {
                            required: "Menu is required.",
                        },
                        sub_menu_id: {
                            required: "Sub Menu is required.",
                        }
                    },
                    submitHandler: function (form) {
                        $('#saveBtn').html('Sending..');
                        $.ajax({
                            data: $('#dataForm').serialize(),
                            url: "{{ url('permissions/store') }}",
                            type: "POST",
                            dataType: 'json',
                            success: function (result) {
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
                                    $('#dataForm').trigger("reset");
                                    $('#ajaxModel').modal('hide');
                                    $('.dataTable').DataTable().ajax.reload(null, false);
                                    $('#saveBtn').html('Save');
                                }
                            },
                            error: function (data) {
                                console.log('Error:', data);
                                $('#saveBtn').html('Save');
                            }
                        });
                    }
                });
            });
        });
        // End Create Data

        // Start Edit Data
        $('body').on('click', '.editData', function () {
            var dataId = $(this).data('id');
            $.get("{{ url('permissions/edit') }}" + '/' + dataId, function (data) {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').remove();
                $('label.error').remove();
                $('#dataFormUpdate').find('.error').removeClass('error');

                $('#data_id').val(data.data.id);
                $('#modelHeadingUpdate').html("Edit branch");
                $('#updateBtn').val("edit-product-category");
                $('#ajaxModelUpdate').modal('show');
                $('.alert-danger').hide();
                $('#name2').val(data.data.slug.split("-").join(" ").replace(/\w\S*/g, function (txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
                }));
                $('#description2').val(data.data.description);
                $('#code2').val(data.data.name);
            })
        });

        $('#updateBtn').click(function (e) {
            e.preventDefault();
            $(this).html('Updating..');
            $.ajax({
                data: $('#dataFormUpdate').serialize(),
                url: "{{ url('permissions/update') }}",
                type: "POST",
                dataType: 'json',
                success: function (result) {
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
                        $('#dataFormUpdate').trigger("reset");
                        $('#ajaxModelUpdate').modal('hide');
                        $('.dataTable').DataTable().ajax.reload(null, false);
                        $('#updateBtn').html('Update');
                    }
                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#saveBtn').html('Save');
                }
            });
        });
        // End Edit Data

        // Start Delete Data
        $('body').on('click', '.deleteData', function () {
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
                        url: "{{ url('permissions/delete') }}" + '/' + data_id,
                        success: function (data) {
                            toastr.success("Permission Deleted!");
                            $('.dataTable').DataTable().ajax.reload(null, false);
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                }
            })



        });
        // End Delete Data
    </script>
    <script>
        $(document).ready(function () {
            $('#module_id').on('change', function () {
                var moduleId = $(this).val();
                if (moduleId) {
                    $.ajax({
                        url: '{{ url("/permissions/getMenu") }}' + '/' + moduleId,
                        type: 'GET',

                        success: function (data) {
                            $('#menu_id').empty();
                            if (data && Object.keys(data.data).length > 0) {
                                $('#menu_id').append("<option selected='' disabled></option>");
                                $.each(data.data, function (key, value) {
                                    $('#menu_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                                $("#menu_id").trigger("change");
                            } else {
                                $('#menu_id').html('<option> No Data Available </option>');
                            }
                        }
                    });
                }
            });
            $('#menu_id').on('change', function () {
                var menuId = $(this).val();
                if (menuId) {
                    $.ajax({
                        url: '{{ url("/permissions/getSubMenu") }}' + '/' + menuId,
                        type: 'GET',
                        success: function (data) {
                            $('#sub_menu_id').empty();
                            if (data && Object.keys(data.data).length > 0) {
                                $('#sub_menu_id').append("<option selected='' disabled></option>");
                                $.each(data.data, function (key, value) {
                                    $('#sub_menu_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                                });
                            } else {
                                $('#sub_menu_id').html('<option> No Data Available </option>');
                            }
                        }
                    });
                }
            });
        });

        $('#module_id, #menu_id, #sub_menu_id').select2({
            dropdownParent: $('#ajaxModel'),
            placeholder: "Select Option",
            // allowClear: true
        }).val(null).trigger('change');
    </script>

@endsection
