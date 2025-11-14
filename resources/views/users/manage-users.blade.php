@extends('website.master')

@section('header_css')
    {{-- Add any page-specific CSS here --}}
@endsection

@section('title', 'Manage User')

@section('content')
<style>
    ul.custom-list{
        top: 0px !important;
    }
    .single-search-input {
        width: 100%;
        padding: 4px 8px;
        box-sizing: border-box;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                {!! Session::has('success') ? '<div class="alert alert-success alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . Session::get("success") . '</div>' : '' !!}
                {!! Session::has('error') ? '<div class="alert alert-danger alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . Session::get("error") . '</div>' : '' !!}
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Manage User</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">User Role</li>
                            <li class="breadcrumb-item active">User</li>
                            <li class="breadcrumb-item active">Manage User</li>
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
                        <h5 class="card-title mb-0">User List</h5>
                    </div>
                    <div class="card-body">
                        <div class="">
                            <table class="table table-bordered data-table" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th width="3%">Sl No</th>
                                        <th>User Name</th>
                                        <th>City Corporation</th>
                                        <th>Ward</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>created_at</th>
                                        <th>updated_at</th>
                                        <th>Status</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </tfoot>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end row-->

        <!--- Start Create Modal --->
        <div class="modal fade modal-lg" id="ajaxModel" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="createUserLabel">Create User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                    </div>

                    <form id="dataForm" name="dataForm" class="tablelist-form" autocomplete="off">
                        <div class="alert alert-danger" style="display:none"></div>
                        <div class="modal-body">

                            <div class="mb-3">
                                <label for="name" class="form-label">User Name <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter User Name" maxlength="255" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter User Email" maxlength="255" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" maxlength="255" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Repeat Password <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repeat Password" maxlength="255" required>
                                </div>
                            </div>

                            {{-- Employee lookup --}}
                            <div class="mb-3">
                                <label for="emp_id_text" class="form-label">Employee</label>
                                <div class="col-sm-12">
                                    <input class="form-control" type="text" placeholder="Enter Employee" id="emp_id_text" name="emp_id_text" autocomplete="off">
                                    <div id="employee_list" class="searchEmployeeSection"></div>
                                    <input type="hidden" id="emp_id" name="emp_id">
                                    <input type="hidden" id="emp_manual_id" name="emp_manual_id">
                                </div>
                            </div>

                            {{-- City Corporation --}}
                            <div class="mb-3">
                                <label for="city_corporation_id" class="form-label">City Corporation</label>
                                <div class="col-sm-12">
                                    <select id="city_corporation_id" name="city_corporation_id" class="form-select">
                                        <option value="">-- Select City Corporation --</option>
                                        @foreach($cityCorporations as $cc)
                                            <option value="{{ $cc->id }}">{{ $cc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Ward (dependent) --}}
                            <div class="mb-3">
                                <label for="ward_id" class="form-label">Ward</label>
                                <div class="col-sm-12">
                                    <select id="ward_id" name="ward_id" class="form-select">
                                        <option value="">-- Select Ward --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="roles" class="form-label">Roles <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <select name="roles_id[]" id="roles_id" class="form-control" multiple="multiple" style="width: 100%" required>
                                        @foreach($roleList as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <div class="hstack gap-2 justify-content-start">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--- End Create Modal--->

        <!--- Start Update Modal--->
        <div class="modal fade modal-lg" id="ajaxModelUpdate" tabindex="-1" aria-labelledby="updateUserLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light p-3">
                        <h5 class="modal-title" id="updateUserLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                    </div>

                    <form id="dataFormUpdate" name="dataFormUpdate" class="tablelist-form" autocomplete="off">
                        <div class="alert alert-danger" id="updateError" style="display:none"></div>
                        <div class="modal-body">
                            <input type="hidden" name="data_id" id="data_id">

                            <div class="mb-3">
                                <label for="name2" class="form-label">User Name <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" id="name2" name="name" maxlength="255" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email2" class="form-label">Email</label>
                                <div class="col-sm-12">
                                    <input type="email" class="form-control" id="email2" name="email" maxlength="255" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password2" class="form-label">Password</label>
                                <div class="col-sm-12">
                                    <input type="password" id="password2" name="password" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password2" class="form-label">Confirm Password</label>
                                <div class="col-sm-12">
                                    <input type="password" id="confirm_password2" name="confirm_password" class="form-control">
                                </div>
                            </div>

                            <input type="hidden" id="emp_id2" name="employee_id">

                            {{-- City Corporation (update) --}}
                            <div class="mb-3">
                                <label for="city_corporation_id2" class="form-label">City Corporation</label>
                                <div class="col-sm-12">
                                    <select id="city_corporation_id2" name="city_corporation_id" class="form-select">
                                        <option value="">-- Select City Corporation --</option>
                                        @foreach($cityCorporations as $cc)
                                            <option value="{{ $cc->id }}">{{ $cc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Ward (update dependent) --}}
                            <div class="mb-3">
                                <label for="ward_id2" class="form-label">Ward</label>
                                <div class="col-sm-12">
                                    <select id="ward_id2" name="ward_id" class="form-select">
                                        <option value="">-- Select Ward --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="roles_id2" class="form-label">Roles <span class="text-danger">*</span></label>
                                <div class="col-sm-12">
                                    <select name="roles_id[]" id="roles_id2" class="form-control" multiple="multiple" style="width: 100%" required>
                                        @foreach($roleList as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status2" class="form-label">Status</label>
                                <div class="col-sm-12">
                                    <select name="status" class="form-control form-select" id="status2">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <div class="hstack gap-2 justify-content-start">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success" id="updateBtn">Update</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <!--- End Update Modal--->

    </div>
</div>

@endsection

@section('footer_js')

<script>
    // CSRF for jQuery ajax
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Initialize DataTable
    $('.data-table').DataTable({
        language: {
            paginate: { next: '&#8594;', previous: '&#8592;' }
        },
        processing: true,
        serverSide: true,
        iDisplayLength: 25,
        aaSorting: [['0', 'desc']],
        dom: '<"toolbar">Bfr<"topip"ip>t<"bottomip"ip>',
        ajax: '{{ url("users/manage-users") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'name', name: 'name' },
            { data: 'cityCorporation', name: 'cityCorporation' },
            { data: 'ward', name: 'ward' },
            { data: 'email', name: 'email' },
            { data: 'role_id', name: 'role_id' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
            { data: 'users.status', name: 'users.status', default: '' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        initComplete: function() {
            this.api().columns([1,2,3,4,5,6]).every(function() {
                var column = this;
                var input = document.createElement("input");
                input.classList.add("single-search-input");
                $(input).appendTo($(column.footer()).empty())
                    .on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search(val ? val : '', true, false).draw();
                    });
            });

            this.api().columns([7]).every(function() {
                var column = this;
                var select = $('<select class="single-search-input"><option value=""></option></select>')
                    .appendTo($(column.footer()).empty())
                    .on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search(val ? '^' + val + '$' : '', true, false).draw();
                    });
                column.each(function() {
                    select.append('<option value="Active">Active</option>');
                    select.append('<option value="Inactive">Inactive</option>');
                });
            });

            if (@json(auth()->user()->can('000258'))) {
                $("div.toolbar").html("<a class='btn btn-success btnAdd' href='javascript:void(0)' onclick='showForm()'> <i class='fas fa-plus'></i></a>");
            }
        }
    });

    // Show Create Form
    function showForm() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('label.error').remove();
        $('#dataForm').find('.error').removeClass('error');

        $('#dataForm').trigger("reset");
        $('#emp_id').val('');
        $('#emp_manual_id').val('');
        $('#roles_id').val(null).trigger('change');
        $('#roles_id2').val(null).trigger('change');
        $('#ward_id').html('<option value="">-- Select Ward --</option>');
        $('#ward_id2').html('<option value="">-- Select Ward --</option>');
        $('#ajaxModel').modal('show');
    }

    // Prepare Select2
    $(document).ready(function () {
        $('#roles_id').select2({ dropdownParent: $('#ajaxModel'), placeholder: "Select Roles" }).val(null).trigger('change');
        $('#roles_id2').select2({ dropdownParent: $('#ajaxModelUpdate'), placeholder: "Select Roles" }).val(null).trigger('change');
    });

    // Create (Save) Handler
    $(document).ready(function () {
        $('#saveBtn').click(function (e) {
            $("#dataForm").validate({
                rules: {
                    name: { required: true, maxlength: 40 },
                    email: { required: true, email: true, maxlength: 40 },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: "#password" },
                    "roles_id[]": { required: true }
                },
                messages: {
                    name: { required: "name is required.", maxlength: "name cannot exceed 40 characters." },
                    email: { required: "Email address is required.", email: "Provide a valid email address.", maxlength: "Email address cannot exceed 40 characters." },
                    password: { required: "Password is required.", minlength: "Password must be at least 8 characters long." },
                    password_confirmation: { required: "Password confirmation is required.", equalTo: "Passwords do not match." },
                    "roles_id[]": { required: "At least one role must be selected." }
                },
                submitHandler: function (form) {
                    $('#saveBtn').html('Sending..');
                    $.ajax({
                        data: $('#dataForm').serialize(),
                        url: "{{ url('users/manage-users/store') }}",
                        type: "POST",
                        dataType: 'json',
                        success: function(result) {
                            $('.form-control').removeClass('is-invalid');
                            $('.invalid-feedback').remove();

                            if (result.errors) {
                                $('#saveBtn').html('Save');
                                $.each(result.errors, function (field, messages) {
                                    const inputField = $('#' + field);
                                    inputField.addClass('is-invalid');
                                    inputField.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                                });
                            } else {
                                if (result.success) {
                                    $('#dataForm').trigger("reset");
                                    $('#ajaxModel').modal('hide');
                                    $('.data-table').DataTable().ajax.reload(null, false);
                                    $('#saveBtn').html('Save');
                                    toastr.success(result.message);
                                } else {
                                    toastr.error(result.message);
                                }
                            }
                        },
                        error: function(data) {
                            $.each(data.responseJSON.errors, function(key, value) {
                                const inputField = $('#' + key);
                                inputField.addClass('is-invalid');
                                inputField.after('<div class="invalid-feedback">' + value[0] + '</div>');
                            });
                            $('#saveBtn').html('Save');
                        }
                    });
                }
            });
        });

        // Update handler
        $('#updateBtn').click(function (e) {
            $("#dataFormUpdate").validate({
                rules: {
                    name: { required: true },
                    "roles_id[]": { required: true },
                    status: { required: true }
                },
                messages: {
                    name: { required: "name is required." },
                    "roles_id[]": { required: "Roles are required" },
                    status: { required: "Status is required" }
                },
                submitHandler: function (form) {
                    $('#updateBtn').html('Updating..');
                    $.ajax({
                        data: $('#dataFormUpdate').serialize(),
                        url: "{{ url('users/manage-users/update') }}",
                        type: "POST",
                        dataType: 'json',
                        success: function(result) {
                            $('.form-control').removeClass('is-invalid');
                            $('.invalid-feedback').remove();

                            if (result.errors) {
                                $('#updateBtn').html('Update');
                                $.each(result.errors, function (field, messages) {
                                    const inputField = $('#' + field + '2');
                                    inputField.addClass('is-invalid');
                                    inputField.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                                });
                            } else {
                                if (result.success) {
                                    $('#dataFormUpdate').trigger("reset");
                                    $('#ajaxModelUpdate').modal('hide');
                                    $('.data-table').DataTable().ajax.reload(null, false);
                                    $('#updateBtn').html('Update');
                                    toastr.success(result.message);
                                } else {
                                    toastr.error(result.message);
                                }
                            }
                        },
                        error: function(data) {
                            toastr.error(data.responseJSON.message);
                            $('#updateBtn').html('Update');
                        }
                    });
                }
            });
        });

    });

    // Edit: populate update modal
    $('body').on('click', '.editData', function() {
        var dataId = $(this).data('id');
        $.get("{{ url('users/manage-users/edit') }}" + '/' + dataId, function(data) {
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('label.error').remove();
            $('#dataFormUpdate').find('.error').removeClass('error');

            $('#data_id').val(data.employee.id);
            $('#ajaxModelUpdate').modal('show');
            $('#name2').val(data.employee.name);
            $('#email2').val(data.employee.email);
            $('#emp_id2').val(data.employee.employee_id);
            $('#status2').val(data.employee.status || 'Active');

            // Roles
            let roleIds = data.data.role_id;
            if (roleIds) {
                let roleIdsArray = roleIds.split(',');
                $('#roles_id2').val(roleIdsArray).trigger('change');
            } else {
                $('#roles_id2').val(null).trigger('change');
            }

            // City corp & wards: set selected city corp then load wards & set ward
            const cityCorpId = data.employee.city_corporation_id ?? '';
            const wardId = data.employee.ward_id ?? '';

            if (cityCorpId) {
                $('#city_corporation_id2').val(cityCorpId).trigger('change');
                // after wards loaded, set ward value (loaded in getWards success)
                // store wardId in a temporary data attr so getWards can set it
                $('#city_corporation_id2').data('selected-ward', wardId);
            } else {
                $('#ward_id2').html('<option value="">-- Select Ward --</option>');
            }
        })
    });

    // Delete handler
    $('body').on('click', '.deleteData', function() {
        var data_id = $(this).data("id");
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            }, buttonsStyling: true
        });

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
                    type: "POST",
                    url: "{{ url('users/manage-users/delete') }}" + '/' + data_id,
                    success: function(data) {
                        if (data.success) {
                            $('.data-table').DataTable().ajax.reload(null, false);
                            toastr.success(data.message);
                        } else {
                            $('.data-table').DataTable().ajax.reload(null, false);
                            toastr.error(data.message);
                        }
                    },
                    error: function(data) {
                        toastr.error(data.responseJSON.message);
                    }
                });
            }
        })
    });

    // Employee search (create modal)
    $('#emp_id_text').on('keyup', function() {
        var query = $(this).val();
        $('#emp_id').val('');
        $('#emp_manual_id').val('');
        if (query.length < 1) { $('#employee_list').html(''); return; }
        $.ajax({
            url: "{{ url('search/employeeByNameOrID') }}",
            type: "GET",
            data: { emp: query },
            success: function(data) { $('#employee_list').html(data); }
        })
    });

    $(document).on('click', 'li.searchEmployee', function() {
        var selectedText = $(this).text();
        var emp_id = $(this).val();
        $('#emp_id_text').val(selectedText);
        $('#employee_list').html("");
        var textInput = $('#emp_id_text').val();
        $.ajax({
            url: "{{ url('getEmployeeByNameOrID/empId') }}/" + emp_id,
            type: "GET",
            data: { textInput: textInput },
            success: function(data) {
                $("#emp_id").val(data.emp_id);
                $("#emp_manual_id").val(data.emp_id_no);
            }
        })
    });

    // DEPENDENT DROPDOWNS: load wards for selected city corporation (both create & update)
    function loadWards(cityCorpId, targetSelect, selectedWardId = null) {
        if (!cityCorpId) {
            $(targetSelect).html('<option value="">-- Select Ward --</option>');
            return;
        }
        $(targetSelect).html('<option value="">Loading...</option>');
        fetch('/get-wards/' + cityCorpId)
            .then(response => response.json())
            .then(data => {
                $(targetSelect).empty().append('<option value="">-- Select Ward --</option>');
                data.forEach(function(ward) {
                    $(targetSelect).append('<option value="'+ ward.id +'">'+ (ward.number ?? ward.name ?? ward.id) +'</option>');
                });
                if (selectedWardId) {
                    $(targetSelect).val(selectedWardId).trigger('change');
                }
            })
            .catch(err => {
                $(targetSelect).html('<option value="">Error loading wards</option>');
            });
    }

    // when city corp changes in create modal
    $(document).on('change', '#city_corporation_id', function() {
        let cityCorpId = $(this).val();
        loadWards(cityCorpId, '#ward_id');
    });

    // when city corp changes in update modal
    $(document).on('change', '#city_corporation_id2', function() {
        let cityCorpId = $(this).val();
        loadWards(cityCorpId, '#ward_id2');
    });

    // When edit modal opens, if city_corporation_id2 has a data-selected-ward attribute, use it
    // (getWards will set ward after loading)
    // We already set data-selected-ward earlier when opening edit modal.

    // Select2 initialization done earlier. Close employee dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('employee_list');
        const input = document.getElementById('emp_id_text');
        if (dropdown && !dropdown.contains(event.target) && input && !input.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
</script>

@endsection
