<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-height: 60px; }
        .company { font-size: 18px; font-weight: bold; }
        .meta { margin-top: 8px; font-size: 12px; }
        .receipt { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .receipt td, .receipt th { padding: 8px; border: 1px solid #ddd; }
        .receipt th { background: #f7f7f7; text-align: left; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 16px; }
        .small { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('app_assets/images/logo.png')))
            <img src="{{ public_path('app_assets/images/logo.png') }}" class="logo" alt="Logo">
        @endif
        <div class="company">{{ config('app.name', 'Waste Management') }}</div>
        <div class="meta">Payment Receipt</div>
    </div>

    <table style="width:100%;">
        <tr>
            <td>
                <strong>Receipt ID:</strong> {{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}<br>
                <strong>Paid By:</strong> {{ $payment->user?->name ?? 'N/A' }}<br>
                <strong>Phone:</strong> {{ $payment->user?->phone ?? 'N/A' }}
            </td>
            <td>
                <strong>Payment Date:</strong> {{ $payment->paid_at ? $payment->paid_at->format('d M Y, H:i') : ($payment->created_at ? $payment->created_at->format('d M Y, H:i') : 'N/A') }}<br>
                <strong>Payment Method:</strong> {{ ucfirst($payment->payment_method ?? 'N/A') }}<br>
                <strong>Reference:</strong> {{ $payment->payment_reference ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="receipt">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount (Tk)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Bill - {{ $payment->cityCorporation?->title ?? 'N/A' }} (Ward {{ $payment->ward?->number ?? 'N/A' }}) for {{ $payment->payment_month ? \Carbon\Carbon::parse($payment->payment_month)->format('M Y') : 'N/A' }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="total">Total</td>
                <td class="text-right total">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="small">This is a system generated receipt. For any queries please contact the City Corporation office.</p>

</body>
</html>
