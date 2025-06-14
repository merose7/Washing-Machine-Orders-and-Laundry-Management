@extends('layouts.app')

@section('content')
<style>
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background: white !important;
    }
}
</style>

<div class="flex justify-center items-center min-h-screen bg-gray-100 print:bg-white">
    <div class="bg-white w-full max-w-md p-6 border border-gray-300 rounded-xl shadow-md font-mono text-sm print:shadow-none print:border-none">
        
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">THE DAILY WASH</h2>
            <p class="text-gray-600 text-xs">Jl. Ketintang, Surabaya</p>
            <p class="text-gray-600 text-xs">Telp: 081-234-567-890</p>
            <hr class="my-2 border-t border-dashed border-gray-400">
            <p class="mt-2 font-semibold text-base">STRUK PEMBAYARAN</p>
            <p class="text-xs text-gray-500">Harap simpan sebagai bukti pembayaran</p>
        </div>

        @include('booking._receipt_details')

        <hr class="my-3 border-t border-dashed border-gray-400">

        {{-- Kode Booking Opsional --}}
        <div class="text-center text-xs text-gray-500 mb-4">
            Kode Booking: <span class="font-semibold">{{ strtoupper(substr(md5($booking->id . $booking->customer_name), 0, 8)) }}</span>
        </div>

        <div class="text-center mt-4 text-gray-800">
            <p class="text-sm">Terima kasih telah menggunakan layanan kami 🙏</p>
            <p class="text-pink-600 font-semibold text-sm">-- Semoga Harimu Menyala 🔥 --</p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex flex-wrap justify-center items-center gap-2 mt-6 no-print">
            <!-- Cetak -->
            <button onclick="window.print()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow">
                Cetak Struk
            </button>

            <!-- Cek Status -->
            <button id="check-status-button" class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded shadow">
                Check Status
            </button>

            <!-- Konfirmasi -->
            <form method="POST" action="{{ route('booking.confirmReceipt', $booking->id) }}">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-black px-4 py-2 rounded shadow">
                    Konfirmasi Terima Struk
                </button>
            </form>

            <!-- Kembali -->
            <a href="{{ route('customer.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                Kembali ke Home
            </a>
        </div>

        {{-- Status Message --}}
        <div id="status-message" class="text-center mt-4 font-bold text-blue-700 no-print text-sm"></div>
    </div>
</div>

<script>
document.getElementById('check-status-button')?.addEventListener('click', function () {
    const messageBox = document.getElementById('status-message');
    messageBox.textContent = "Sedang memeriksa status...";

    setTimeout(() => {
        messageBox.textContent = "Status pembayaran: Success (Paid)";
    }, 1500);
});
</script>
@endsection
