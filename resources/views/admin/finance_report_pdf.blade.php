<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Report PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan - Pemasukan Booking Bulanan</h1>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Cash Income</th>
                <th>Midtrans Income</th>
                <th>Total Income</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $row)
            <tr>
                <td>{{ $row['month'] }}</td>
                <td>Rp {{ number_format($row['cash'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['midtrans'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
