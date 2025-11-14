@extends('website.master')

@section('title', 'Assign Collector')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        <h4 class="mb-3">Assign Collector</h4>

        <form method="POST" action="{{ route('waste-requests.assign', $wasteRequest->id) }}">
            @csrf

            {{-- Collector Dropdown --}}
            <label class="form-label">Choose Collector</label>
            <select class="form-select" name="collector_id" id="collector_id" required>
                <option value="">Select Collector</option>
                @foreach($collectors as $collector)
                    <option value="{{ $collector->id }}">
                        {{ $collector->name }}
                    </option>
                @endforeach
            </select>

            {{-- Result Box --}}
            <div id="collector-info" class="alert alert-info mt-3" style="display: none;">
                <strong id="collectorName"></strong> currently has 
                <span id="inProgressCount" class="fw-bold"></span> in-progress requests.
            </div>

            <button class="btn btn-success mt-3">Assign</button>
        </form>

    </div>
</div>
@endsection

@section('footer_js')
<script>
document.getElementById('collector_id').addEventListener('change', function() {

    let collectorId = this.value;

    if (!collectorId) {
        document.getElementById('collector-info').style.display = 'none';
        return;
    }

    // Fetch collector workload
    fetch(`/collector/in-progress-count/${collectorId}`)
        .then(response => response.json())
        .then(data => {

            document.getElementById('collectorName').innerText = data.name;
            document.getElementById('inProgressCount').innerText = data.count;

            document.getElementById('collector-info').style.display = 'block';
        });
});
</script>
@endsection
