<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        h1, h2, h3 { text-align: center; }
        .totals { margin-top: 20px; font-weight: bold; }
        .totals div { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan</h1>

    <h3>Pembayaran Cash</h3>
    <table>
        <thead>
            <tr>
                <th>ID Pembayaran</th>
                <th>Mesin ID</th>
                <th>Nama Customer</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumCash = 0;
            @endphp
            @foreach($cashPayments as $payment)
                @php
                    $sumCash += $payment->amount ?? 0;
                @endphp
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking && $payment->booking->machine ? $payment->booking->machine->name : '-' }}</td>
                    <td>{{ $payment->booking ? $payment->booking->customer_name : '-' }}</td>
                    <td>Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">Total Pembayaran Cash:</th>
                <th>Rp {{ number_format($sumCash, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <h3>Pembayaran Midtrans</h3>
    <table>
        <thead>
            <tr>
                <th>ID Transaksi</th>
                <th>Mesin ID</th>
                <th>Nama Customer</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumMidtrans = 0;
            @endphp
            @foreach($midtransPayments as $payment)
            @php
                $amount = ($payment->amount && $payment->amount > 0) ? $payment->amount : 10000;
                $sumMidtrans += $amount;
            @endphp
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->booking && $payment->booking->machine ? $payment->booking->machine->name : '-' }}</td>
                <td>{{ $payment->booking ? $payment->booking->customer_name : '-' }}</td>
                <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">Total Pembayaran Midtrans:</th>
                <th>Rp {{ number_format($sumMidtrans, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="totals">
        <div>Total Pembayaran Cash: Rp {{ number_format($totalCashPayments ?? $sumCash, 0, ',', '.') }}</div>
        <div>Total Pembayaran Midtrans: Rp {{ number_format($totalMidtransPayments ?? $sumMidtrans, 0, ',', '.') }}</div>
        <div>Total Pemasukan: Rp {{ number_format($totalPayments ?? ($sumCash + $sumMidtrans), 0, ',', '.') }}</div>
    </div>
</body>
</html>
