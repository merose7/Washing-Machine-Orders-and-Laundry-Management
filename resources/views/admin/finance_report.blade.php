
@extends('layouts.admin')

@section('title', 'Dashboard Laporan Keuangan')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container my-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3">
            <h3>Dashboard Laporan Keuangan</h3>
            <div class="d-flex flex-column flex-md-row gap-2">
                {{-- Removed Export to PDF button as per user request --}}

                {{-- Toggle Chart Type --}}
                <div class="btn-group" role="group" id="chartTypeGroup">
                    <button type="button" class="btn btn-outline-secondary active" data-type="bar">Bar</button>
                    <button type="button" class="btn btn-outline-secondary" data-type="line">Line</button>
                </div>
            </div>
        </div>

    <form method="GET" action="{{ route('admin.financeReport') }}" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="filter_date" class="col-form-label">Tanggal</label>
                <input type="date" id="filter_date" name="filter_date" class="form-control" value="{{ request('filter_date') }}">
            </div>
            <div class="col-auto align-self-end">
                <button type="submit" class="btn btn-success">Filter</button>
                <a href="{{ route('admin.financeReport') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="bg-danger bg-opacity-10 rounded p-4 shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <h5 class="text-muted mb-1">Total Pemasukan</h5>
                <h2 class="totalPayments">Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</h2>
            </div>
            
        </div>
        <div style="height: 300px;">
            <canvas id="salesChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 col-md-4">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="mb-1 text-muted">Total Pembayaran Cash</div>
                <h4 class="text-success">Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="mb-1 text-muted">Total Pembayaran Midtrans</div>
                <h4 class="text-warning">Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="mb-1 text-muted">Total Pemasukan</div>
                <h5 class="totalPayments">Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</h5>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesChart');
    let chart;

    const monthlyData = {
        labels: @json(array_column($reportData ?? [], 'month')),
        datasets: [
            {
                label: 'Cash',
                data: @json(array_column($reportData ?? [], 'cash')),
                borderColor: 'green',
                backgroundColor: 'rgba(75, 192, 192, 0.3)',
                fill: false,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
            },
            {
                label: 'Midtrans',
                data: @json(array_column($reportData ?? [], 'midtrans')),
                borderColor: 'blue',
                backgroundColor: 'rgba(54, 162, 235, 0.3)',
                fill: false,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3,
            }, 
        ]
    };

    function createChart(data, type = 'bar') {
        if (chart) chart.destroy();
        chart = new Chart(ctx, {
            type: type,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e0e0e0',
                        },
                        ticks: {
                            font: {
                                size: 14,
                            },
                            callback: value => 'Rp ' + value.toLocaleString('id-ID')
                        }
                    },
                    x: {
                        grid: {
                            color: '#f0f0f0',
                        },
                        ticks: {
                            font: {
                                size: 14,
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: context => 'Rp ' + context.parsed.y.toLocaleString('id-ID')
                        }
                    }
                }
            }
        });
    }

    createChart(monthlyData, 'bar'); 

    const buttons = document.querySelectorAll('#viewToggleGroup button');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;

            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const view = button.getAttribute('data-view');
            if (view === 'daily') {
                window.location.href = "{{ route('admin.financeReport.daily') }}";
            } else if (view === 'monthly') {
                window.location.href = "{{ route('admin.financeReport') }}";
            } else if (view === 'yearly') {
                alert('Laporan tahunan belum tersedia.');
            }
        });
    });

    // Toggle chart type
    const chartTypeButtons = document.querySelectorAll('#chartTypeGroup button');
    chartTypeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            chartTypeButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const type = btn.getAttribute('data-type');
            createChart(monthlyData, type);
        });
    });
});
</script>
@endsection
