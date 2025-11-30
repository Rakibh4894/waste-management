@extends('website.master')

@section('title', 'Recycling Process Details')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Recycle Process #{{ $process->id }}</h3>
                <p class="text-muted mb-0">
                    Waste Request ID: {{ $process->wasteRequest?->id ?? '-' }} |
                    <span class="text-capitalize">{{ $process->status }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('recycle-process.index') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line"></i> Back
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif


        <div class="row g-4">

            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">

                {{-- Process Overview --}}
                <div class="card mb-3 shadow-sm border-primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Process Overview</h5>
                            <small class="text-muted">
                                Created At:
                                <strong>{{ $process->created_at }}</strong>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge fs-6 px-3 py-2 text-white
                                @switch($process->recycle_status)
                                    @case('waiting_for_sorting') bg-warning @break
                                    @case('sorting_completed') bg-primary @break
                                    @case('sent_to_recycling') bg-info @break
                                    @case('recycling_in_process') bg-secondary @break
                                    @case('recycled') bg-success @break
                                    @case('cancelled') bg-danger @break
                                    @default bg-dark
                                @endswitch
                            ">
                                {{ ucfirst($process->recycle_status) }}
                            </span>

                            @if($process->remarks)
                                <br><small class="text-muted"><i>{{ $process->remarks }}</i></small>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recyclable Items --}}
                @if(count($process->recycleItems) > 0)
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Recyclable Items</strong></div>
                    <div class="card-body">
                        @if($process->recycleItems->count() > 0)
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Type</th>
                                        <th>Item Name</th>
                                        <th>Weight (kg)</th>
                                        <th>Probable Value</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($process->recycleItems) > 0)
                                        @foreach($process->recycleItems as $item)
                                            <tr>
                                                <td>{{ ucfirst($item->item_type) }}</td>
                                                <td>{{ $item->item_name }}</td>
                                                <td>{{ $item->weight }}</td>
                                                <td>{{ $item->value }}</td>
                                                <td>{{ $item->notes }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mb-0">No items added for this recycle process.</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Process Details --}}
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Process Details</strong></div>
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Initial Estimated Weight</small>
                                <div class="fw-semibold">{{ $process->estimated_weight ?? '-' }} kg</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Sorted Weight</small>
                                <div>{{ $process->sorted_weight ?? '-' }} kg</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Recycled Weight</small>
                                <div>{{ $process->recycled_weight ?? '-' }} kg</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Processing Duration</small>
                                <div>
                                    {{ $process->processing_time ?? '-' }}
                                </div>
                            </div>
                        </div>

                        @if($process->remarks)
                        <div class="mt-3">
                            <small class="text-muted">Remarks</small>
                            <div class="p-3 border rounded bg-light">{{ $process->remarks }}</div>
                        </div>
                        @endif

                    </div>
                </div>

                {{-- Uploaded Images --}}
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Uploaded Images</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if(isset($process->images) && count($process->images) > 0)
                                @forelse($process->images as $img)
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <div class="border rounded overflow-hidden shadow-sm cursor-pointer"
                                            onclick="openImageModal('{{ asset($img->image_path) }}')">
                                            <img src="{{ asset($img->image_path) }}" class="img-fluid"
                                                style="height:150px;width:100%;object-fit:cover;">
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">No images uploaded.</p>
                                @endforelse
                            @endif
                        </div>
                    </div>
                </div>

            </div> {{-- END LEFT COL --}}

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-4">

                {{-- Summary --}}
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Summary</strong></div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Associated Waste Request</small>
                            <div class="fw-semibold">#{{ $process->wasteRequest?->id ?? '-' }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Recyclable</small>
                            <div class="fw-semibold">
                                <span class="badge {{ $process->is_recyclable ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $process->is_recyclable ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Status</small>
                            <div class="fw-semibold text-capitalize">{{ $process->status }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Started At</small>
                            <div class="text-muted">{{ $process->start_time ?? '-' }}</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Completed At</small>
                            <div class="text-muted">{{ $process->completed_time ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Process Timeline</strong></div>
                    <div class="card-body">

                        @php
                            $steps = [
                                'waiting_for_sorting'      => 'Waiting For Sorting',
                                'sorting_completed'        => 'Sorting Completed',
                                'sent_to_recycling'        => 'Sent to Recycling',
                                'recycling_in_process'     => 'Recycling In Process',
                                'recycled'                 => 'Recycled',
                                'cancelled'                => 'Cancelled'
                            ];
                            $current = $process->status;
                        @endphp

                        <div class="timeline d-flex flex-column gap-3">
                            @foreach($steps as $k => $label)
                                <div class="d-flex align-items-center">
                                    <div style="
                                        width:28px; height:28px; border-radius:50%;
                                        display:flex; align-items:center; justify-content:center;
                                        background: {{ $k === $current || array_search($k,array_keys($steps)) <= array_search($current,array_keys($steps)) ? '#0d6efd' : '#e9ecef' }};
                                        color: {{ $k === $current || array_search($k,array_keys($steps)) <= array_search($current,array_keys($steps)) ? '#fff' : '#6c757d' }};
                                    ">
                                        <i class="ri-checkbox-blank-circle-fill" style="font-size:10px;"></i>
                                    </div>
                                    <div class="ms-3 fw-semibold {{ $k === $current ? 'text-primary' : 'text-muted' }}">
                                        {{ $label }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

            </div> {{-- END RIGHT COL --}}

        </div>
    </div>
</div>


{{-- Image Modal --}}
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark p-0">
            <img id="previewImg" src="" class="w-100 rounded">
        </div>
    </div>
</div>
@endsection



@section('footer_js')
<script>
function openImageModal(src){
    document.getElementById('previewImg').src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection
