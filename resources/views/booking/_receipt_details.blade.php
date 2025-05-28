<div class="mb-4 font-mono text-sm">
    <p>Tanggal     : {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <p>Nama       : {{ $booking->customer_name }}</p>
    <p>Mesin ID   : {{ $booking->machine_id }}</p>
    <p>Waktu Booking : {{ \Carbon\Carbon::parse($booking->booking_time)->format('d/m/Y H:i') }}</p>
</div>

<div class="mb-4 font-mono text-sm">
    <hr class="my-2 border-dashed border-gray-400">
    <p>Metode Bayar : {{ ucfirst($booking->payment_method ?? 'N/A') }}</p>
    <p>Status Bayar : 
        @if(strtolower($booking->payment_method) === 'midtrans' && in_array(strtolower($booking->payment_status), ['success', 'paid', 'settlement']))
            Paid
        @else
            {{ ucfirst($booking->payment_status ?? 'Belum Bayar') }}
        @endif
    </p>
    <p>Harga Booking : 
        @if(isset($booking->price))
            Rp {{ number_format($booking->price, 0, ',', '.') }}
        @else
            Rp 10.000
        @endif
    </p>
    <hr class="my-2 border-dashed border-gray-400">
</div>
