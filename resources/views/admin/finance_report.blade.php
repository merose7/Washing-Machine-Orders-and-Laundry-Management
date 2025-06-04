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

<div style="width: 100%; max-width: 900px; margin: auto;">
    <canvas id="financeChart" style="width: 100%; height: 400px;"></canvas>
</div>

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
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Cash Income',
                    data: cashData,
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    stack: 'Stack 0',
                    maxBarThickness: 50,
                },
                {
                    label: 'Midtrans Income',
                    data: midtransData,
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    stack: 'Stack 0',
                    maxBarThickness: 50,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Booking Income (Stacked)'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
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
