@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Pembayaran Midtrans</h1>

    <div class="bg-white shadow-md rounded p-6">
        <p>Booking ID: {{ $booking->id }}</p>
        <p>Nama Pelanggan: {{ $booking->customer_name }}</p>
        <p>Mesin ID: {{ $booking->machine_id }}</p>
        <p>Tanggal dan Waktu Booking: {{ $booking->booking_time }}</p>
        <p>Metode Pembayaran: Midtrans</p>

        <button id="pay-button" class="mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Bayar Sekarang
        </button>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
document.getElementById("pay-button").addEventListener("click", function() {
    fetch('/payment/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            name: "{{ $booking->customer_name }}",
            email: "{{ auth()->user()->email ?? '' }}",
            phone: ""
        })
    })
    .then(response => response.json())
    .then(data => {
        window.snap.pay(data.snap_token, {
            onSuccess: function(result){ console.log("Success", result); },
            onPending: function(result){ console.log("Pending", result); },
            onError: function(result){ console.log("Error", result); },
            onClose: function(){ alert("Popup ditutup tanpa transaksi."); }
        });
    });
});
</script>
@endsection
