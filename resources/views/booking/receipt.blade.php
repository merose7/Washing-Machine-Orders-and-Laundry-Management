@extends('layouts.app')

@section('content')
<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
<div class="flex justify-center">
    <div class="bg-white w-full max-w-md p-6 border border-gray-300 font-mono text-sm">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold">THE DAILY WASH</h2>
            <p>Jl. Ketintang, Surabaya</p>
            <p>Telp: 081-234-567-890</p>
            <hr class="my-2 border-dashed border-gray-400">
        </div>

        @include('booking._receipt_details')

        <div class="text-center mt-4 no-print">
            <!-- Check Status Button -->
            <button id="check-status-button" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Check Status
            </button>
            <div id="status-message" style="margin-top: 10px; font-weight: bold;"></div>
        </div>

        <div class="text-center mt-6">
            <p>Terima kasih telah menggunakan layanan kami.</p>
            <p>-- Semoga Harimu Menyala ❤️‍🔥❤️‍🔥 --</p>
        </div>

        <div class="flex justify-center mt-6 space-x-2 no-print">
            <!-- Tombol Cetak Struk -->
            <button onclick="window.print()" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded">
                Cetak Struk
            </button>
        </div>

        <div class="text-center mt-4 no-print">
            <!-- Tombol Konfirmasi Terima Struk -->
            <form method="POST" action="{{ route('booking.confirmReceipt', $booking->id) }}">
                @csrf
                <button type="submit" class="btn text-black">
                    Konfirmasi Terima Struk
                </button>
            </form>
        </div>

        <div class="text-center mt-4 no-print">
            <!-- Tombol Kembali ke Home -->
            <a href="{{ route('customer.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded ">
                Kembali ke Home
            </a>
        </div>
    </div>
</div>

<script>
const baseUrl = "{{ url('') }}";
const bookingId = {{ $booking->id }};
const statusMessage = document.getElementById('status-message');
const checkStatusButton = document.getElementById('check-status-button');

function updatePaymentStatusDisplay(status) {
    const statusParagraphs = document.querySelectorAll('p');
    statusParagraphs.forEach(p => {
        if (p.textContent.includes('Status Bayar')) {
            p.textContent = "Status Bayar : " + status.charAt(0).toUpperCase() + status.slice(1);
        }
    });
}

function checkPaymentStatus() {
    statusMessage.textContent = "Checking payment status...";
    fetch(baseUrl + '/booking/payment/status/' + bookingId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                statusMessage.textContent = "Payment status: " + data.payment_status;
                // Update the payment status display in receipt details
                const statusText = (data.payment_status === 'paid' || data.payment_status === 'success') ? 'Paid' : data.payment_status.charAt(0).toUpperCase() + data.payment_status.slice(1);
                updatePaymentStatusDisplay(statusText);
                if (data.payment_status === 'paid' || data.payment_status === 'success') {
                    alert("Pembayaran berhasil!");
                    window.location.reload();
                }
            } else if (data.error) {
                statusMessage.textContent = "Error: " + data.error;
            } else {
                statusMessage.textContent = "Unknown response from server.";
            }
        })
        .catch(error => {
            statusMessage.textContent = "Failed to check payment status.";
        });
}

if (checkStatusButton) {
    checkStatusButton.addEventListener('click', checkPaymentStatus);
}

// Optional: Poll payment status every 30 seconds
setInterval(() => {
    checkPaymentStatus();
}, 30000);
</script>
@endsection
