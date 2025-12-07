@extends('website.master')

@section('title', 'Add Bill Payment')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Add Bill Payment</h4>
        </div>

        {!! Session::has('success') ? '<div class="alert alert-success alert-dismissible">'. Session::get("success") .'</div>' : '' !!}
                {!! Session::has('error') ? '<div class="alert alert-danger alert-dismissible"></button>'. Session::get("error") .'</div>' : '' !!}

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Create Bill Payment</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('payments.proceed') }}">
                    @csrf

                    <div class="row">

                        {{-- City Corporation --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City Corporation <span class="text-danger">*</span></label>

                            @if($cityCorporationName != "")
                                {{ $cityCorporationName }}
                                <input type="hidden" name="city_corporation_id" value="{{ $user->city_corporation_id }}">
                            @else
                                <select name="city_corporation_id" id="city_corporation_id" class="form-select" required>
                                    <option value="">-- Select City Corporation --</option>
                                    @foreach($cityCorporations as $cc)
                                        <option value="{{ $cc->id }}">{{ $cc->title }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @error('city_corporation_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Ward --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ward <span class="text-danger">*</span></label>

                            @if($wardNumber != "")
                                {{ $wardNumber }}
                                <input type="hidden" name="ward_id" value="{{ $user->ward_id }}">
                            @else
                                <select name="ward_id" id="ward_id" class="form-select" required>
                                    <option value="">-- Select Ward --</option>
                                </select>
                            @endif

                            @error('ward_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Bill Month --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bill Month <span class="text-danger">*</span></label>
                            <input type="month" name="bill_month" class="form-control" required>
                            @error('bill_month') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (BDT) <span class="text-danger">*</span></label>
                            <input type="number" id="amount" name="amount" class="form-control" readonly placeholder="Auto-filled based on Ward">
                            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary">Proceed Payment</button>
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
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
document.addEventListener("DOMContentLoaded", function () {
    const citySelect = document.getElementById("city_corporation_id");
    const wardSelect = document.getElementById("ward_id");

    if (citySelect) {
        citySelect.addEventListener("change", function () {
            let cityCorpId = this.value;
            wardSelect.innerHTML = '<option value="">Loading...</option>';

            fetch('/get-wards/' + cityCorpId)
                .then(res => res.json())
                .then(data => {
                    wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';
                    data.forEach(ward => {
                        wardSelect.innerHTML += `<option value="${ward.id}">${ward.number}</option>`;
                    });
                });
        });
    }
    
    // Autofill amount when ward selected
    const amountInput = document.getElementById('amount');
    wardSelect?.addEventListener('change', function() {
        const wardId = this.value;
        const cityId = citySelect ? citySelect.value : '';
        if (!cityId || !wardId) {
            amountInput.value = '';
            return;
        }

        fetch(`/monthly-bill/active-amount?city_id=${cityId}&ward_id=${wardId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.raw !== undefined) {
                    amountInput.value = data.raw;
                } else {
                    amountInput.value = '';
                }
            }).catch(()=> amountInput.value = '');
    });
});
</script>
@endsection
