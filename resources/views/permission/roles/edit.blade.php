@extends('website.master')
@section('header_css')

@endsection
@section('title')
    Assign User Roles
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Assign User Roles</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Assign User Role</li>
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
                            <h5 class="card-title mb-0">Assign User Roles</h5>
                        </div>
                        <div class="card-body">
                            <div
                                class="card-body">
                                {!! Form::open(['url' => "roles/update/$role->id", 'method' => 'POST', 'id' => 'roleForm']) !!}
                                @csrf
                                <div class="row border p-3 mb-3" style="border-color: #ADBC7A; border-width: 1px;">
                                    <div class="col-lg-3">
                                        <input type="hidden" id="role_id" value="{{ $role->id }}">
                                        <div class="form-group">
                                            <label for="name" class="control-label">Name <span
                                                    style="color: red;">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                   placeholder="Enter Name" value="{{ $role->name }}" maxlength=""
                                                   readonly required>
                                        </div>

                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="module" class="control-label">Module <span
                                                    style="color: red;">*</span></label>
                                            <div>
                                                <select class="form-control" name="module_id" id="module" required>
                                                    <option selected="">Select One</option>
                                                    @foreach ($modules as $module)
                                                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="menu" class="control-label">Menu</label>
                                            <div>
                                                <select class="form-control" name="menu_id" id="menu"></select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="sub_menu" class="control-label">Sub Menu</label>
                                            <div>
                                                <select class="form-control" name="sub_menu_id" id="sub_menu"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-12">
                                        <button type="button" id="checkAll" class="btn btn-primary mb-2">Assign All
                                        </button>
                                        <button type="button" id="uncheckAll" class="btn btn-danger mb-2">Revoke All
                                        </button>
                                        <div id="checkboxRow" class="row">
                                            <!-- Checkbox items will be dynamically inserted here -->
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end row-->


        </div>
        <!-- container-fluid -->


    </div>
@endsection



@section('footer_js')

    <script src="{{ asset('app_assets/vendors/js/forms/validation/jquery.validate.min.js') }}"></script>
    <script>
        $(document).ready(function () {

            $("#roleForm").validate({
                errorPlacement: function () {
                    return false;
                }
            });

            //Select2
            $(".permissionSelect2").select2({
                //maximumSelectionLength: 1
            });
        });
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
            $('#module').on('change', function () {
                var moduleId = $(this).val();
                var role_id = document.getElementById("role_id").value;
                if (moduleId && role_id) {
                    $.ajax({
                        url: '{{ url('/permissions/getMenu') }}',
                        type: 'POST',
                        data: {
                            'module_id': moduleId,
                            'role_id': role_id,
                        },
                        success: function (data) {
                            $('#menu').empty();
                            if (data && Object.keys(data.data).length > 0) {
                                $('#menu').append(
                                    "<option selected='' disabled>Select One</option>");
                                $.each(data.data, function (key, value) {
                                    $('#menu').append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                            } else {
                                $('#menu').html('<option> No Data Available </option>');
                            }
                            $("#checkboxRow").html(data.grid)
                        }
                    });
                }
            });
            $('#menu').on('change', function () {
                var menuId = $(this).val();
                var role_id = document.getElementById("role_id").value;
                if (menuId) {
                    $.ajax({
                        url: '{{ url('/permissions/getSubMenu') }}',
                        type: 'POST',
                        data: {
                            'module_id': menuId,
                            'role_id': role_id,
                        },
                        success: function (data) {
                            $('#sub_menu').empty();
                            if (data && Object.keys(data.data).length > 0) {
                                $('#sub_menu').append(
                                    "<option selected='' >Select One</option>");
                                $.each(data.data, function (key, value) {
                                    $('#sub_menu').append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                            } else {
                                $('#sub_menu').html('<option> No Data Available </option>');
                            }
                            $("#checkboxRow").html(data.grid)
                        }
                    });
                }
            });
            $('#sub_menu').on('change', function () {
                var sub_menuId = $(this).val();
                var role_id = document.getElementById("role_id").value;
                if (sub_menuId) {
                    $.ajax({
                        url: '{{ url('/permissions/getSubMenuPermission') }}',
                        type: 'POST',
                        data: {
                            'module_id': sub_menuId,
                            'role_id': role_id,
                        },
                        success: function (data) {
                            $("#checkboxRow").html(data.grid)
                        }
                    });
                }
            });
        });

        $(document).ready(function () {

            let checkedValues = [];
            // Function to check all checkboxes and assign permissions
            $('#checkAll').click(function () {
                var role_id = document.getElementById("role_id").value;
                $('#checkboxRow input[type="checkbox"]').each(function () {
                    if (!this.checked) {
                        checkedValues.push($(this).val());
                        $(this).prop('checked', true);
                    }
                });

                if (checkedValues.length > 0) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        buttonsStyling: true
                    })

                    swalWithBootstrapButtons.fire({
                        title: 'Are you sure?',
                        text: "",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Assign All!',
                        confirmButtonColor: '#2385BA',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.value) {
                            event.preventDefault();
                            $.ajax({
                                type: "POST",
                                url: "{{ url('roles/assign-batch-permission-to-role') }}",
                                dataType: 'json',
                                data: {
                                    _token: $('input[name="_token"]').val(),
                                    role_id: role_id,
                                    permissions: checkedValues
                                },
                                success: function (data) {
                                    toastr.success("Permissions Assigned!");
                                },
                                error: function (data) {
                                    console.log('Error:', data);
                                }
                            });
                        } else {
                            // If the user clicks "Cancel", uncheck the checkboxes and clear the array
                            $('#checkboxRow input[type="checkbox"]').each(function () {
                                if (checkedValues.includes($(this).val())) {
                                    $(this).prop('checked', false);
                                }
                            });
                            checkedValues = []; // Clear the checkedValues array
                        }
                    })
                }

            });

            // Function to uncheck all checkboxes and revoke permissions
            $('#uncheckAll').click(function () {
                var role_id = document.getElementById("role_id").value;
                let checkedValues = [];
                $('#checkboxRow input[type="checkbox"]').each(function () {
                    if (this.checked) {
                        checkedValues.push($(this).val());
                        $(this).prop('checked', false);
                    }
                });

                if (checkedValues.length > 0) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        buttonsStyling: true
                    })

                    swalWithBootstrapButtons.fire({
                        title: 'Are you sure?',
                        text: "",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Revoke All!',
                        confirmButtonColor: '#2385BA',
                        cancelButtonText: 'No, cancel!',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.value) {
                            event.preventDefault();
                            $.ajax({
                                type: "POST",
                                url: "{{ url('roles/revoke-batch-permission-to-role') }}",
                                dataType: 'json',
                                data: {
                                    _token: $('input[name="_token"]').val(),
                                    role_id: role_id,
                                    permissions: checkedValues
                                },
                                success: function (data) {
                                    toastr.success("Permissions Revoked!");
                                },
                                error: function (data) {
                                    console.log('Error:', data);
                                }
                            });
                        } else {
                            // If the user clicks "Cancel", uncheck the checkboxes and clear the array
                            $('#checkboxRow input[type="checkbox"]').each(function () {
                                if (checkedValues.includes($(this).val())) {
                                    $(this).prop('checked', true);
                                }
                            });
                            checkedValues = []; // Clear the checkedValues array
                        }
                    })
                }

            });

            // Function to handle the assign and revoke of permissions
            function showAlert(checkbox) {
                var permissionId = checkbox.value;
                var role_id = document.getElementById("role_id").value;
                var isChecked = checkbox.checked;
                if (isChecked) {
                    // Assign Permission
                    $.ajax({
                        type: "POST",
                        url: "{{ url('roles/assign-permission-to-role') }}" + '/' + permissionId,
                        dataType: 'json',
                        data: {
                            _token: $('input[name="_token"]').val(),
                            role_id: role_id
                        },
                        success: function (data) {
                            toastr.success("Permission Assigned!");
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                } else {
                    // Revoke Permission
                    $.ajax({
                        type: "POST",
                        url: "{{ url('roles/revoke-permission-from-role') }}" + '/' + permissionId,
                        dataType: 'json',
                        data: {
                            _token: $('input[name="_token"]').val(),
                            role_id: role_id
                        },
                        success: function (data) {
                            toastr.success("Permission Revoked!");
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                }
            }

            // Triggering AJAX requests on checkbox state change
            $('#checkboxRow').on('change', 'input[type="checkbox"]', function () {
                showAlert(this);
            });
        });
    </script>

@endsection
