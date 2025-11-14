@extends('website.master')

@section('header_css')
@endsection

@section('title')
    Assign User Roles
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Assign User Roles</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Assign User Role</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Role Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Assign Permissions</h5>
                    </div>
                    <div class="card-body">
                        {!! Form::open(['url' => "roles/update/$role->id", 'method' => 'POST', 'id' => 'roleForm']) !!}
                        @csrf
                        <input type="hidden" id="role_id" value="{{ $role->id }}">

                        <!-- Role Name (readonly) -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Role Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $role->name }}" readonly>
                        </div>

                        <!-- Permissions Checkboxes -->
                        <div class="mb-3">
                            <button type="button" id="checkAll" class="btn btn-primary mb-2">Assign All</button>
                            <button type="button" id="uncheckAll" class="btn btn-danger mb-2">Revoke All</button>
                            <div id="checkboxRow" class="row">
                                <!-- Permission checkboxes will be dynamically inserted here via AJAX -->
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
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

    $(document).ready(function () {
        let checkedValues = [];

        // Assign All
        $('#checkAll').click(function () {
            checkedValues = [];
            $('#checkboxRow input[type="checkbox"]').each(function () {
                if (!this.checked) {
                    checkedValues.push($(this).val());
                    $(this).prop('checked', true);
                }
            });
            if (checkedValues.length) {
                assignBatch(checkedValues);
            }
        });

        // Revoke All
        $('#uncheckAll').click(function () {
            checkedValues = [];
            $('#checkboxRow input[type="checkbox"]').each(function () {
                if (this.checked) {
                    checkedValues.push($(this).val());
                    $(this).prop('checked', false);
                }
            });
            if (checkedValues.length) {
                revokeBatch(checkedValues);
            }
        });

        // Individual checkbox change
        $('#checkboxRow').on('change', 'input[type="checkbox"]', function () {
            let permissionId = $(this).val();
            if ($(this).is(':checked')) {
                assignPermission(permissionId);
            } else {
                revokePermission(permissionId);
            }
        });

        // AJAX functions
        function assignPermission(permissionId) {
            $.post("{{ url('roles/assign-permission-to-role') }}/" + permissionId, {
                role_id: $('#role_id').val()
            }, function () {
                toastr.success("Permission Assigned!");
            });
        }

        function revokePermission(permissionId) {
            $.post("{{ url('roles/revoke-permission-from-role') }}/" + permissionId, {
                role_id: $('#role_id').val()
            }, function () {
                toastr.success("Permission Revoked!");
            });
        }

        function assignBatch(permissionIds) {
            $.post("{{ url('roles/assign-batch-permission-to-role') }}", {
                role_id: $('#role_id').val(),
                permissions: permissionIds
            }, function () {
                toastr.success("Permissions Assigned!");
            });
        }

        function revokeBatch(permissionIds) {
            $.post("{{ url('roles/revoke-batch-permission-to-role') }}", {
                role_id: $('#role_id').val(),
                permissions: permissionIds
            }, function () {
                toastr.success("Permissions Revoked!");
            });
        }

        // Load all permissions for this role via AJAX
        $.post("{{ url('/permissions/getAllForRole') }}", { role_id: $('#role_id').val() }, function (data) {
            $('#checkboxRow').html(data.grid); // Server should return HTML checkboxes
        });
    });
</script>
@endsection
