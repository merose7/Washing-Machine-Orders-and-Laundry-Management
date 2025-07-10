<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Pemasukan PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        h1, h2, h3 { text-align: center; }
        .totals { margin-top: 20px; }
        .totals div { margin-bottom: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Detail Pemasukan Laundry The Daily Wash</h1>

    <h3>Pembayaran Cash</h3>
    <table>
        <thead>
            <tr>
                <th>ID Pembayaran</th>
                <th>Mesin ID</th>
                <th>Nama Customer</th>
                <th>Jumlah (Rp)</th>
                <th>Waktu Booking</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashPayments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->booking && $payment->booking->machine ? $payment->booking->machine->name : '-' }}</td>
                <td>{{ $payment->booking ? $payment->booking->customer_name : '-' }}</td>
                <td>Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                <td>{{ $payment->booking ? $payment->booking->booking_time : '-' }}</td>
                <td>{{ ucfirst($payment->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total Pembayaran Cash:</th>
                <th>Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>

    <h3>Pembayaran Midtrans</h3>
    <table>
        <thead>
            <tr>
                <th>ID Pembayaran</th>
                <th>Mesin ID</th>
                <th>Nama Customer</th>
                <th>Waktu Booking</th>
                <th>Status</th>
                <th>Total Pemasukan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($midtransPayments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->booking && $payment->booking->machine ? $payment->booking->machine->name : '-' }}</td>
                <td>{{ $payment->booking ? $payment->booking->customer_name : '-' }}</td>
                <td>{{ $payment->booking ? $payment->booking->booking_time : '-' }}</td>
                <td>{{ $payment->booking ? ucfirst($payment->booking->status) : '-' }}</td>
                <td>Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Total Pembayaran Midtrans:</th>
                <th>Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="totals">
        <div>Total Pembayaran Cash: Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</div>
        <div>Total Pembayaran Midtrans: Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</div>
        <div>Total Pemasukan: Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</div>
    </div>
</body>
</html>
