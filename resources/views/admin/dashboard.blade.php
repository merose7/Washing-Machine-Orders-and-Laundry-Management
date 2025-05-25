@extends('layouts.admin')

@section('title', 'Dashboard Admin')
<head>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

</head>
@section('content_header')
    <h1>Dashboard Admin Laundry</h1>
    <p>Selamat datang di panel admin</p>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div id="totalMachinesBox" class="small-box bg-info" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $totalMachines }}</h3>
                <p>Total Mesin Cuci</p>
            </div>
            <div class="icon">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="small-box-footer">
                Klik untuk lihat detail <i class="fas fa-arrow-circle-right"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div id="totalBookingsBox" class="small-box bg-success" style="cursor:pointer;">
            <div class="inner">
                <h3>{{ $totalBookings }}</h3>
                <p>Total Booking</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="small-box-footer">
                Klik untuk lihat detail <i class="fas fa-arrow-circle-right"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>Rp {{ number_format($totalPayments, 0, ',', '.') }}</h3>
                <p>Total Pembayaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <a href="{{ url('admin/payments') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalNotifications }}</h3>
                <p>Gmail Notifikasi</p>
            </div>
            <div class="icon">
                <i class="fas fa-envelope"></i>
            </div>
            <a href="{{ url('admin/notifications') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div id="machinesListSection" style="display:none; margin-top: 20px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Mesin Cuci</h3>
        </div>
        <div class="card-body table-responsive" style="max-height: 400px; overflow-x: auto; padding: 1rem;">
            <table id="machinesTable" class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Machine::all() as $machine)
                    <tr>
                        <td>{{ $machine->name }}</td>
                        <td>
                            @if($machine->status === 'available')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i> Tersedia
                                </span>
                            @elseif($machine->status === 'booked')
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i> Digunakan
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-tools me-1"></i> Perbaikan
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="bookingsListSection" style="display:none; margin-top: 20px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Booking</h3>
        </div>
        <div class="card-body table-responsive" style="max-height: 400px; overflow-x: auto; padding: 1rem;">
            <table id="bookingsTable" class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>Nama Customer</th>
                        <th>Mesin</th>
                        <th>Waktu Booking</th>
                        <th>Status</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Booking::with('machine')->get() as $booking)
                    <tr>
                        <td>{{ $booking->customer_name }}</td>
                        <td>{{ $booking->machine ? $booking->machine->name : '-' }}</td>
                        <td>{{ $booking->booking_time }}</td>
                        <td>{{ ucfirst($booking->status) }}</td>
                        <td>{{ ucfirst($booking->payment_status) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#machinesTable').DataTable({
        pagingType: 'simple_numbers', // => < 1 2 3 > style
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

    $('#bookingsTable').DataTable({
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

    const totalMachinesBox = document.getElementById('totalMachinesBox');
    const machinesListSection = document.getElementById('machinesListSection');
    const totalBookingsBox = document.getElementById('totalBookingsBox');
    const bookingsListSection = document.getElementById('bookingsListSection');

    totalMachinesBox.addEventListener('click', function () {
        if (machinesListSection.style.display === 'none' || machinesListSection.style.display === '') {
            machinesListSection.style.display = 'block';
            totalMachinesBox.querySelector('.small-box-footer').innerHTML = 'Sembunyikan detail <i class="fas fa-arrow-circle-up"></i>';
        } else {
            machinesListSection.style.display = 'none';
            totalMachinesBox.querySelector('.small-box-footer').innerHTML = 'Klik untuk lihat detail <i class="fas fa-arrow-circle-right"></i>';
        }
    });

    totalBookingsBox.addEventListener('click', function () {
        if (bookingsListSection.style.display === 'none' || bookingsListSection.style.display === '') {
            bookingsListSection.style.display = 'block';
            totalBookingsBox.querySelector('.small-box-footer').innerHTML = 'Sembunyikan detail <i class="fas fa-arrow-circle-up"></i>';
        } else {
            bookingsListSection.style.display = 'none';
            totalBookingsBox.querySelector('.small-box-footer').innerHTML = 'Klik untuk lihat detail <i class="fas fa-arrow-circle-right"></i>';
        }
    });
});
</script>
@endpush
