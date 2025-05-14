@extends('layouts.app')

@section('content')
<div class="flex justify-center">
    <div class="bg-white w-full max-w-md p-6 border border-gray-300 font-mono text-sm">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold">THE DAILY WASH</h2>
            <p>Jl. Ketintang, Surabaya</p>
            <p>Telp: 081-234-567-890</p>
            <hr class="my-2 border-dashed border-gray-400">
        </div>

        @include('booking._receipt_details')

        <div class="text-center">
            <p>Terima kasih telah menggunakan layanan kami.</p>
            <p>-- Semoga Harimu Menyala ❤️‍🔥❤️‍🔥--</p>
        </div>

        <div class="flex justify-center mt-4 space-x-2">
    <!-- Tombol Cetak Struk -->
    <button onclick="window.print()" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded">
        Cetak Struk
    </button>
    </div>

    <div class="text-center mt-4">
    <!-- Tombol Kembali ke Home -->
    <a href="{{ route('customer.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded ">
        Kembali ke Home
    </a>
    </div>
    
</div>
@endsection
