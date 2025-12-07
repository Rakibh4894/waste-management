@extends('website.master')

@section('title', 'Payment Receipt View')

@section('content')
<div class="page-content">
    <div class="container">
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">{{ config('app.name', 'Waste Management') }}</h5>
                        <small class="text-muted">Payment Receipt</small>
                    </div>
                    <div>
                        @if(file_exists(public_path('app_assets/images/logo.png')))
                            <img src="{{ asset('app_assets/images/logo.png') }}" alt="Logo" style="max-height:60px;">
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Receipt ID:</strong> {{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Paid By:</strong> {{ $payment->user?->name ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $payment->user?->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>Payment Date:</strong> {{ $payment->paid_at ? $payment->paid_at->format('d M Y, H:i') : ($payment->created_at ? $payment->created_at->format('d M Y, H:i') : 'N/A') }}</p>
                        <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method ?? 'N/A') }}</p>
                        <p><strong>Reference:</strong> {{ $payment->payment_reference ?? '-' }}</p>
                    </div>
                </div>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount (Tk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Monthly Bill - {{ $payment->cityCorporation?->title ?? 'N/A' }} (Ward {{ $payment->ward?->number ?? 'N/A' }}) for {{ $payment->payment_month ? \Carbon\Carbon::parse($payment->payment_month)->format('M Y') : 'N/A' }}</td>
                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Total</strong></td>
                            <td class="text-end"><strong>{{ number_format($payment->amount, 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between mt-4">
                    <div class="small text-muted">This is a system generated receipt. Contact City Corporation for queries.</div>
                    <div>
                        <a href="{{ route('payments.receipt', $payment->id) }}" class="btn btn-primary" target="_blank">Download PDF</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
