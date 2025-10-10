@extends('website.master')
@section('header_css')

@endsection
@section('title')
    Assign / Revoke Permission
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
                        <h4 class="mb-sm-0">Assign / Revoke Permission</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active">User Role</li>
                                <li class="breadcrumb-item active">User</li>
                                <li class="breadcrumb-item active">Manage Users Permission</li>
                                <li class="breadcrumb-item active">Assign / Revoke Permission</li>
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
                            <h5 class="card-title mb-0">Assign / Revoke Permission To : <b style="color: #2385ba;">{{ \App\Models\User::getUserNameById($id) }}</b></h5>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <table class="table table-bordered data-table" style="width: 100%">
                                    <thead>
                                    <tr>
                                        <th width="3%">SL</th>
                                        <th>Name</th>
                                        <th>Permission</th>
                                        <th>Assign / Revoke</th>
                                    </tr>
                                    </thead>

                                    <tfoot>
                                    <tr>
                                        <th width="3%"></th>
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



    </div>
@endsection



@section('footer_js')
    <script>
        $(function () {
            var permissionsList = $('.table').DataTable({
                iDisplayLength: 25,
                processing: true,
                serverSide: true,
                searching: true,
                dom: '<"toolbar">Bfr<"topip"ip>t<"bottomip"ip>',
                ajax: {
                    url:  '{{ url('/users/get-user-permissions-list') }}',
                    method:'POST',
                    data: function (d) {
                        d._token = $('input[name="_token"]').val();
                        d.user_id = <?php echo $id; ?>

                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {data: 'slug', name: 'slug'},
                    {data: 'name', name: 'name'},
                    {data: 'assign_revoke', name: 'assign_revoke', orderable: false, searchable: false}
                ],
                initComplete: function() {
                    this.api().columns([1,2]).every(function() {
                        var column = this;
                        var input = document.createElement("input");
                        input.classList.add("single-search-input");
                        $(input).appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? val : '', true, false).draw();
                            });
                    });
                }
            });

            // Start Assign
            $('body').on('click', '.assignPermission', function() {
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
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Assign it!',
                    confirmButtonColor: '#2385BA',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        event.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "{{ url('users/assign-permission') }}" + '/' + data_id,
                            data: {
                                _token  : $('input[name="_token"]').val(),
                                user_id : <?php echo $id; ?>
                            },
                            success: function(data) {
                                toastr.success("Permission Assigned!");
                                permissionsList.ajax.reload(null, false);
                            },
                            error: function(data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                })

            });
            // End Assign

            // Start Revoke
            $('body').on('click', '.revokePermission', function() {
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
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Revoke it!',
                    confirmButtonColor: '#f63636',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        event.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: "{{ url('users/revoke-permission') }}" + '/' + data_id,
                            data: {
                                _token  : $('input[name="_token"]').val(),
                                user_id : <?php echo $id; ?>
                            },
                            success: function(data) {
                                toastr.success("Permission Revoked!");
                                permissionsList.ajax.reload(null, false);
                            },
                            error: function(data) {
                                console.log('Error:', data);
                            }
                        });
                    }
                })

            });
            // End Revoke


        });

    </script>


@endsection
