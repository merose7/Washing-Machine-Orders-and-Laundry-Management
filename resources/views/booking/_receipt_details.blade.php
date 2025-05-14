<div class="mb-4">
    <p>Tanggal     : {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <p>Nama       : {{ $booking->customer_name }}</p>
    <p>Mesin ID   : {{ $booking->machine_id }}</p>
    <p>Waktu Booking : {{ \Carbon\Carbon::parse($booking->booking_time)->format('d/m/Y H:i') }}</p>
</div>

<div class="mb-4">
    <hr class="my-2 border-dashed border-gray-400">
    <p>Metode Bayar : {{ ucfirst($booking->payment_method ?? 'N/A') }}</p>
    <p>Status Bayar : {{ ucfirst($booking->payment_status ?? 'Belum Bayar') }}</p>
    <p>Harga Booking : Rp 10.000</p>
    <hr class="my-2 border-dashed border-gray-400">
</div>
