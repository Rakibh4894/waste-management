
@extends('website.master')
@section('header_css')

@endsection
@section('title')
    Manage Users Permission
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
                        <h4 class="mb-sm-0">Manage Users Permission</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
                                <li class="breadcrumb-item active">User Role</li>
                                <li class="breadcrumb-item active">User</li>
                                <li class="breadcrumb-item active">Manage Users Permission</li>
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
                                        <th width="3%">SL</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th width="10%">Action</th>
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
            $('.table').DataTable({
                iDisplayLength: 25,
                processing: true,
                serverSide: true,
                searching: true,
                dom: '<"toolbar">Bfr<"topip"ip>t<"bottomip"ip>',
                ajax: {
                    url:  '{{ url('/users/get-users-for-permission') }}',
                    method:'get',
                    data: function (d) {
                        d._token = $('input[name="_token"]').val();
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
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
        });

    </script>

@endsection
