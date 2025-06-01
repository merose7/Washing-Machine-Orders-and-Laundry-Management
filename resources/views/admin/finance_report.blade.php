@extends('layouts.admin')

@section('title', 'Finance Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Finance Report - Monthly Booking Income</h1>

    <div class="mb-4">
    <a href="{{ route('admin.financeReport.exportPdf') }}" class="btn btn-danger me-2">
        <i class="fas fa-file-pdf me-1"></i> Export to PDF
    </a>
    <a href="{{ route('admin.financeReport.exportExcel') }}" class="btn btn-success">
        <i class="fas fa-file-excel me-1"></i> Export to Excel
    </a>
    </div>

    <canvas id="financeChart" width="800" height="400"></canvas>

    <table class="min-w-full bg-white border border-gray-300 mt-6">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b border-gray-300">Month</th>
                <th class="py-2 px-4 border-b border-gray-300">Cash Income</th>
                <th class="py-2 px-4 border-b border-gray-300">Midtrans Income</th>
                <th class="py-2 px-4 border-b border-gray-300">Total Income</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $row)
            <tr>
                <td class="py-2 px-4 border-b border-gray-300">{{ $row['month'] }}</td>
                <td class="py-2 px-4 border-b border-gray-300">Rp {{ number_format($row['cash'], 0, ',', '.') }}</td>
                <td class="py-2 px-4 border-b border-gray-300">Rp {{ number_format($row['midtrans'], 0, ',', '.') }}</td>
                <td class="py-2 px-4 border-b border-gray-300 font-bold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="py-4 text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const reportData = @json($reportData);

    const labels = reportData.map(row => row.month);
    const cashData = reportData.map(row => row.cash);
    const midtransData = reportData.map(row => row.midtrans);
    const totalData = reportData.map(row => row.total);

    const ctx = document.getElementById('financeChart').getContext('2d');
    const financeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Cash Income',
                    data: cashData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Midtrans Income',
                    data: midtransData,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Total Income',
                    data: totalData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Booking Income'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
