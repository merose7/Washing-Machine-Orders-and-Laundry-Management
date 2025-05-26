@extends('layouts.admin')

@section('title', 'Detail Total Pembayaran')

@section('content_header')
    <h1>Detail Total Pembayaran</h1>
    <p>Rincian pembayaran cash dan Midtrans beserta insight total pemasukan</p>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-lg-4 col-6">
<div class="small-box bg-success">
            <div class="inner">
                <h3 id="totalCashPayments">Rp {{ number_format($totalCashPayments, 0, ',', '.') }}</h3>
                <p>Total Pembayaran Cash</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>Rp {{ number_format($totalMidtransPayments, 0, ',', '.') }}</h3>
                <p>Total Pembayaran Midtrans</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
<div class="small-box bg-warning">
            <div class="inner">
                <h3 id="totalPayments">Rp {{ number_format($totalPayments, 0, ',', '.') }}</h3>
                <p>Total Pemasukan</p>
            </div>
            <div class="icon">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Pembayaran Cash</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped text-center align-middle" id="cashPaymentsTable">
            <thead>
                <tr>
                    <th>ID Pembayaran</th>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Jumlah (Rp)</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
                <tbody>
                @foreach($cashPayments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking_id }}</td>
                    <td>
                        @if($payment->customer_name)
                            {{ $payment->customer_name }}
                        @else
                            @php
                                $booking = \App\Models\Booking::find($payment->booking_id);
                            @endphp
                            {{ $booking ? $booking->customer_name : '-' }}
                        @endif
                    </td>
                    <td>10,000</td>
                    <td>{{ $payment->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                </tr>
                @endforeach
                </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pembayaran Midtrans</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped text-center align-middle" id="midtransPaymentsTable">
            <thead>
                <tr>
                    <th>ID Pembayaran</th>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Jumlah (Rp)</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($midtransPayments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking_id }}</td>
                    <td>{{ $payment->customer_name ?? '-' }}</td>
                    <td>{{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td>{{ $payment->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#cashPaymentsTable').DataTable({
        pagingType: 'simple_numbers',
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            paginate: {
                previous: "← Previous",
                next: " Next →"
            },
            zeroRecords: "Tidak ditemukan data yang cocok",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        }
    });

    $('#midtransPaymentsTable').DataTable({
        pagingType: 'simple_numbers',
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            paginate: {
                previous: "← Previous",
                next: " Next →"
            },
            zeroRecords: "Tidak ditemukan data yang cocok",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        }
    });

    // Polling to update totals every 30 seconds
    setInterval(function() {
        fetch("{{ route('admin.payments.totals') }}")
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalCashPayments').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.totalCashPayments);
                document.getElementById('totalPayments').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.totalPayments);
            })
            .catch(error => console.error('Error fetching payment totals:', error));
    }, 30000);
});
</script>
@endpush
