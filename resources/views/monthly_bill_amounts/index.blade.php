@extends('website.master')

@section('title', 'Monthly Bill Amounts')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Monthly Bill Amounts</h5>
                <a href="{{ route('monthly-bill.create') }}" class="btn btn-success">Add New</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <table class="table table-bordered table-hover" id="monthlyAmountsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>City Corporation</th>
                            <th>Ward</th>
                            <th>Amount (Tk)</th>
                            <th>Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->cityCorporation?->title }}</td>
                            <td>{{ $item->ward?->number }}</td>
                            <td>{{ number_format($item->amount,2) }}</td>
                            <td>{!! $item->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary open-activate-modal" data-id="{{ $item->id }}" data-active="{{ $item->is_active ? 1 : 0 }}">Update</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    <!-- Activate modal -->
    <div class="modal fade" id="activateModal" tabindex="-1" role="dialog" aria-labelledby="activateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activateModalLabel">Update Active</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="activate_item_id" value="">
                    <div class="mb-3">
                        <label>Active</label>
                        <select class="form-select" id="activate_is_active">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div id="activateError" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="activateSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#monthlyAmountsTable').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0,4,5] }
        ]
    });

    // Open modal and populate fields
    $(document).on('click', '.open-activate-modal', function() {
        var id = $(this).data('id');
        var active = $(this).data('active');
        $('#activate_item_id').val(id);
        $('#activate_is_active').val(active ? '1' : '0');
        $('#activateError').hide().text('');
        $('#activateModal').modal('show');
    });

    // Save click - send AJAX PUT to update is_active
    $('#activateSaveBtn').on('click', function() {
        var id = $('#activate_item_id').val();
        var isActive = $('#activate_is_active').val();
        var url = '/monthly-bill/' + id;

        $.ajax({
            url: url,
            type: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                is_active: isActive
            },
            success: function(resp) {
                if (resp && resp.success) {
                    // Show success toastr
                    if (typeof toastr !== 'undefined') {
                        toastr.success(resp.message || 'Updated');
                    }
                    // Update the table row in-place instead of reloading
                    var $btn = $('.open-activate-modal[data-id="' + id + '"]');
                    var $tr = $btn.closest('tr');
                    var activeVal = (isActive === '1' || isActive === 1) ? 1 : 0;
                    var badge = activeVal === 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                    // Active column is the 5th td (zero-based index 4)
                    $tr.find('td').eq(4).html(badge);
                    // Update the button data attribute so next open uses the new value
                    $btn.attr('data-active', activeVal);
                    $btn.data('active', activeVal);
                    // Hide modal and clear any errors
                    $('#activateError').hide().text('');
                    $('#activateModal').modal('hide');
                } else {
                    var err = resp.message || 'Update failed';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(err);
                    }
                    $('#activateError').text(err).show();
                }
            },
            error: function(xhr) {
                console.error(xhr);
                var msg = 'Error updating';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (typeof toastr !== 'undefined') {
                    toastr.error(msg);
                }
                $('#activateError').text(msg).show();
            }
        });
    });
});
</script>
@endsection
