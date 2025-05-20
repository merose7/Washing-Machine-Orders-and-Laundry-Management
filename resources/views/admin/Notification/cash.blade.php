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
                                <th>Judul</th>
                                <th>Pesan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $notification)
                                @php
                                    $isCashConfirm = $notification->title === 'Konfirmasi Pembayaran Cash';
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
                                    <td>{{ $notification->message ?? 'Isi notifikasi' }}</td>
                                    <td>{{ $notification->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($isCashConfirm && $isUnread && $bookingId)
                                            <form method="POST" action="{{ route('admin.booking.confirmCashPayment', $bookingId) }}" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Konfirmasi</button>
                                            </form>
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

            // Auto-close alert
            setTimeout(() => {
                $(".alert").alert('close');
            }, 3000);
        });
    </script>
@endpush
