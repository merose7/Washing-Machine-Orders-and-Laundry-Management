@extends('layouts.app')

@section('content')
<div class="flex justify-center">
    <div class="bg-white w-full max-w-md shadow-lg rounded p-6">
        <h2 class="text-center text-xl font-bold mb-4">Struk Booking Laundry</h2>

        <div class="mb-4 border-b border-gray-300 pb-2">
            <p><strong>Nama Pelanggan:</strong> {{ $booking->customer_name }}</p>
            <p><strong>ID Mesin:</strong> {{ $booking->machine_id }}</p>
            <p><strong>Waktu Booking:</strong> {{ $booking->booking_time }}</p>
        </div>

        <div class="mb-4 border-b border-gray-300 pb-2">
            <p><strong>Metode Pembayaran:</strong> {{ ucfirst($booking->payment_method ?? 'Tidak tersedia') }}</p>
            <p><strong>Status Pembayaran:</strong> {{ ucfirst($booking->payment_status ?? 'Belum Bayar') }}</p>
        </div>

        <div class="text-center mt-4">
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Cetak Struk
            </button>
        </div>
    </div>
</div>
@endsection
