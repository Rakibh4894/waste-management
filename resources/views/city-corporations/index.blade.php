@extends('website.master')

@section('title', 'Manage City Corporations')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Manage City Corporations</h4>
                    <div class="page-title-right">
                        <a href="javascript:void(0)" class="btn btn-success" onclick="showForm()">+ Add City Corporation</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- City Corporation List -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Create Modal -->
    <div class="modal fade modal-lg" id="cityCorporationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="cityCorporationForm">
                    <div class="modal-header">
                        <h5 class="modal-title">City Corporation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="city_corporation_id" name="city_corporation_id">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span style="color: red;">*</span></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter Title" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade modal-lg" id="ajaxModelUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title">Edit City Corporation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataFormUpdate" name="dataFormUpdate" autocomplete="off">
                    <div class="alert alert-danger" id="updateError" style="display:none"></div>
                    <div class="modal-body">
                        <input type="hidden" name="data_id" id="data_id">
                        <div class="mb-3">
                            <label for="title2" class="form-label">Title <span style="color: red;">*</span></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="title2" name="title" placeholder="Enter Title" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span style="color: red;">*</span></label>
                            <div class="col-sm-12">
                                <select name="status" class="form-control" id="status"></select>
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

</div>
@endsection


@section('footer_js')
<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// Load DataTable
let table = $('.data-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ url('city-corporations') }}",
    columns: [
        { data: 'title', name: 'title' },
        { data: 'status', name: 'status',
            render: function(data) {
                return data == 1 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';
            }
        },
        { data: 'action', name: 'action', orderable: false, searchable: false },
    ]
});

// Show Modal (create/edit)
function showForm(id = null) {
    $('#cityCorporationForm')[0].reset();
    $('#city_corporation_id').val('');
    $('#cityCorporationModal').modal('show');
}

// Save
$('#cityCorporationForm').on('submit', function(e) {
    e.preventDefault();
    let id = $('#city_corporation_id').val();
    let url = id ? "city-corporations/update/" + id : "city-corporations/store";

    $.post(url, $(this).serialize(), function(res) {
        $('#cityCorporationModal').modal('hide');
        table.ajax.reload();
        toastr.success(res.message);
    }).fail(err => toastr.error('Something went wrong'));
});

// Edit
$('body').on('click', '.editData', function() {
    var dataId = $(this).data('id');
    $.get("{{ url('city-corporations/edit') }}" + '/' + dataId, function(data) {
        $('#data_id').val(data.data.id);
        $('#title2').val(data.data.title);
        $('#status').html(data.status);
        $('#ajaxModelUpdate').modal('show');
    })
});

// Delete
$('body').on('click', '.deleteData', function() {
    let id = $(this).data('id');
    if (confirm("Are you sure?")) {
        $.post("city-corporations/delete/" + id, function(res) {
            table.ajax.reload();
            toastr.success(res.message);
        }).fail(err => toastr.error('Delete failed'));
    }
});
</script>
@endsection
