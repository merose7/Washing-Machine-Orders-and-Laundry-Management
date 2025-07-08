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
        .totals { margin-top: 20px; }
        .totals div { margin-bottom: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan</h1>
    <h3>Monthly Income Summary</h3>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Cash</th>
                <th>Midtrans</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumCash = 0;
                $sumMidtrans = 0;
                $sumTotal = 0;
            @endphp
            @foreach ($reportData as $data)
            @php
                $sumCash += $data['cash'];
                $sumMidtrans += $data['midtrans'];
                $sumTotal += $data['total'];
            @endphp
            <tr>
                <td>{{ $data['month'] }}</td>
                <td>Rp {{ number_format($data['cash'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($data['midtrans'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>Rp {{ number_format($sumCash, 0, ',', '.') }}</th>
                <th>Rp {{ number_format($sumMidtrans, 0, ',', '.') }}</th>
                <th>Rp {{ number_format($sumTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="totals">
        <div>Total Pembayaran Cash: Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</div>
        <div>Total Pembayaran Midtrans: Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</div>
        <div>Total Pembayaran: Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</div>
    </div>

    
</body>
</html>
