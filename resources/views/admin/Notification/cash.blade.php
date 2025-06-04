@extends('layouts.admin')

@push('styles')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h1 class="mb-0">Notifikasi Pembayaran Cash</h1>
        </div>
        <div class="card-body">

            {{-- Alert Messages --}}
            @foreach (['success', 'error', 'info'] as $type)
                @if (session($type))
                    <div class="alert alert-{{ $type == 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                        <strong>{{ session($type) }}</strong>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            @endforeach

            {{-- Notifikasi Table --}}
            @if($notifications->count())
                <div class="table-responsive">
                    <table id="notificationTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Notifikasi</th>
                                <th>Pesan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $notification)
                                @php
                                    $isCashConfirm = str_contains($notification->title, 'Cash Payment');
                                    $isUnread = !$notification->is_read;
                                    preg_match('/Booking ID (\d+)/', $notification->message, $matches);
                                    $bookingId = $matches[1] ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        @if($isCashConfirm && $isUnread)
                                            <strong>{{ $notification->title }}</strong>
                                        @else
                                            {{ $notification->title ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $notification->message ?? 'Isi notifikasi' }}
                                        <br>
                                        {{-- Debug info --}}
                                        <small>Booking ID: {{ $bookingId }}</small><br>
                                        <small>Notification Title: {{ $notification->title }}</small><br>
                                        @php
                                            $booking = \App\Models\Booking::find($bookingId);
                                        @endphp
                                        <small>Booking Payment Status: {{ $booking ? $booking->payment_status : 'Booking not found' }}</small>
                                    </td>
                                    <td>{{ $notification->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($isCashConfirm && $bookingId)
                                            @if($booking && $booking->payment_status === 'paid')
                                                <span class="badge badge-success">Sudah Bayar</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.booking.confirmCashPayment', $bookingId) }}" class="d-inline-block confirm-cash-payment-form">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Konfirmasi</button>
                                                </form>


                                                <!-- Modal -->
                                                <div class="modal fade" id="editPaymentModal{{ $notification->id }}" tabindex="-1" role="dialog" aria-labelledby="editPaymentModalLabel{{ $notification->id }}" aria-hidden="true">
                                                  <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                      <form method="POST" action="{{ route('admin.notifications.cash.edit', $notification->id) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                          <h5 class="modal-title" id="editPaymentModalLabel{{ $notification->id }}">Edit Status Pembayaran Cash</h5>
                                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                          </button>
                                                        </div>
                                                        <div class="modal-body">
                                                          <div class="form-group">
                                                            <label for="payment_status">Status Pembayaran</label>
                                                            <select class="form-control" id="payment_status" name="payment_status" required>
                                                              <option value="paid">Sudah Bayar</option>
                                                              <option value="unpaid">Belum Bayar</option>
                                                            </select>
                                                          </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                          <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                      </form>
                                                    </div>
                                                  </div>
                                                </div>
                                            @endif
                                        @endif

                                        <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}"
                                              class="d-inline-block form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Tidak ada notifikasi pembayaran cash ditemukan.</div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Init DataTables
            $('#notificationTable').DataTable({
                language: {
                    search: "Search:",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "← Previous",
                        next: "Next →"
                    }
                }
            });

            // SweetAlert2 for Delete Confirmation
            document.querySelectorAll('.form-delete').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Tindakan ini tidak dapat dibatalkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // AJAX form submission for cash payment confirmation
            $('.confirm-cash-payment-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var token = form.find('input[name="_token"]').val();

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: token
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.success,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Update totals on the page
                            $('#totalCashPayments').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.totalCashPayments));
                            $('#totalPayments').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.totalPayments));

                            // Optionally disable the confirm button after success
                            form.find('button[type="submit"]').prop('disabled', true).text('Sudah Bayar');
                        } else if (response.info) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Info',
                                text: response.info,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else if (response.error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengkonfirmasi pembayaran.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Auto-close alert
            setTimeout(() => {
                $(".alert").alert('close');
            }, 3000);
        });
    </script>
@endpush
