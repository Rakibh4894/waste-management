@extends('website.master')

@section('title', 'Edit Monthly Bill Amount')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Monthly Bill Amount</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('monthly-bill.update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>City Corporation</label>
                            <select name="city_corporation_id" id="city_corporation_id" class="form-select" required>
                                <option value="">--Select--</option>
                                @foreach($cityCorporations as $cc)
                                    <option value="{{ $cc->id }}" {{ $item->city_corporation_id == $cc->id ? 'selected' : '' }}>{{ $cc->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Ward</label>
                            <select name="ward_id" id="ward_id" class="form-select" required>
                                <option value="{{ $item->ward_id }}">{{ $item->ward?->number }}</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Amount (Tk)</label>
                            <input type="number" step="0.01" name="amount" value="{{ $item->amount }}" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Active</label>
                            <select name="is_active" class="form-select">
                                <option value="0" {{ !$item->is_active ? 'selected' : '' }}>No</option>
                                <option value="1" {{ $item->is_active ? 'selected' : '' }}>Yes</option>
                            </select>
                        </div>

                        <div class="col-md-12 mt-3">
                            <button class="btn btn-primary">Update</button>
                            <a href="{{ route('monthly-bill.index') }}" class="btn btn-secondary">Cancel</a>
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
document.addEventListener('DOMContentLoaded', function() {
    const city = document.getElementById('city_corporation_id');
    const ward = document.getElementById('ward_id');

    city?.addEventListener('change', function() {
        const id = this.value;
        ward.innerHTML = '<option>Loading...</option>';
        fetch('/get-wards/' + id)
            .then(res => res.json())
            .then(data => {
                ward.innerHTML = '<option value="">--Select--</option>';
                data.forEach(w => {
                    ward.innerHTML += `<option value="${w.id}">${w.number}</option>`;
                });
            });
    });
});
</script>
@endsection
