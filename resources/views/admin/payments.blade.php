@extends('layouts.admin')

@section('title', 'Detail Pemasukan')

@section('content_header')
    <h1>Detail Pemasukan Laundry The Daily Wash</h1>
    <p>Tabel Pembayaran Cash dan Midtrans beserta total pemasukan</p>    
    <a href="{{ route('admin.financeReport.exportPdfDetail') }}" class="btn btn-primary">Export to PDF</a>

@stop

@section('content')
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 col-12">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="totalCashPayments">Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</h3>
                <p>Total Pembayaran Cash</p>
            </div>
            <div class="icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-12">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="totalMidtransPayments">Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</h3>
                <p>Total Pembayaran Midtrans</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12 col-12">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="totalPayments">Rp {{ number_format($totalPayments ?? 0, 0, ',', '.') }}</h3>
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
                    <th>Mesin ID</th>
                    <th>Nama Customer</th>
                    <th>Waktu Booking</th>
                    <th>Status</th>
                    <th>Jumlah(Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashPayments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->booking && $payment->booking->machine ? $payment->booking->machine->name : '-' }}</td>
                    <td>{{ $payment->booking ? $payment->booking->customer_name : '-' }}</td>
                    <td>{{ $payment->booking ? $payment->booking->booking_time : '-' }}</td>
                    <td>
                        @php
                            $status = $payment->status;
                            $badgeClass = 'badge-secondary';
                            if (in_array($status, ['paid', 'completed', 'success'])) {
                                $badgeClass = 'badge-success';
                            } elseif ($status === 'pending') {
                                $badgeClass = 'badge-warning';
                            } elseif (in_array($status, ['failed', 'cancelled', 'unknown'])) {
                                $badgeClass = 'badge-danger';
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total Pembayaran Cash:</th>
                    <th id="totalCashPaymentsFooter">Rp {{ number_format($totalCashPayments ?? 0, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
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
                    <th>Mesin ID</th>
                    <th>Nama Customer</th>
                    <th>Waktu Booking</th>
                    <th>Status</th>
                    <th>Jumlah(Rp)</th>
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
                    <td>Rp {{ number_format(($payment->amount && $payment->amount > 0) ? $payment->amount : 10000, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total Pembayaran Midtrans:</th>
                    <th id="totalMidtransPaymentsFooter">Rp {{ number_format($totalMidtransPayments ?? 0, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@stop

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var cashTable = $('#cashPaymentsTable').DataTable({
        pagingType: 'simple_numbers',
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            paginate: {
                previous: "← Previous",
                next: " Next →"
            },
            zeroRecords: "Belum Ada Pembayaran",
            infoEmpty: "Tidak Ada Pembayaran",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\Rp\s\.]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            // Total over all pages
            var totalCash = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            // Total over this page
            var pageTotalCash = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            // Update footer
            $( api.column( 5 ).footer() ).html(
                'Rp ' + totalCash.toLocaleString('id-ID')
            );

            // Update totalCashPayments box
            $('#totalCashPayments').text('Rp ' + totalCash.toLocaleString('id-ID'));

            // Update totalPayments box by adding midtrans total dynamically
            var midtransTotal = midTable ? midTable.column(5).data().reduce(function(a,b){
                return intVal(a) + intVal(b);
            }, 0) : 0;

            var totalPayments = totalCash + midtransTotal;
            $('#totalPayments').text('Rp ' + totalPayments.toLocaleString('id-ID'));
        }
    });

    var midTable = $('#midtransPaymentsTable').DataTable({
        pagingType: 'simple_numbers',
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            paginate: {
                previous: "← Previous",
                next: " Next →"
            },
            zeroRecords: "Belum Ada Pembayaran",
            infoEmpty: "Tidak Ada Pembayaran",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        },
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();

            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\Rp\s\.]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            // Total over all pages
            var totalMidtrans = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            // Total over this page
            var pageTotalMidtrans = api
                .column( 5, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

            // Update footer
            $( api.column( 5 ).footer() ).html(
                'Rp ' + totalMidtrans.toLocaleString('id-ID')
            );

            // Update totalMidtransPayments box
            $('#totalMidtransPayments').text('Rp ' + totalMidtrans.toLocaleString('id-ID'));

            // Update totalPayments box by adding cash total dynamically
            var cashTotal = cashTable ? cashTable.column(5).data().reduce(function(a,b){
                return intVal(a) + intVal(b);
            }, 0) : 0;

            var totalPayments = totalMidtrans + cashTotal;
            $('#totalPayments').text('Rp ' + totalPayments.toLocaleString('id-ID'));
        }
    });
});
</script>
@endpush
