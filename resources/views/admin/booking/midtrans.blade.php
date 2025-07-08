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

        <div class="text-left mt-4 no-print">
            <!-- Tombol Bayar Sekarang -->
            <button id="pay-button" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded">
                Bayar Sekarang
            </button>
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

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
const baseUrl = "{{ url('') }}";
//tambahan untuk pembayaran koneksi antar server mistrans
const payButton = document.getElementById("pay-button");
if (payButton) {
    payButton.addEventListener("click", function() {
        payButton.disabled = true;
        payButton.textContent = "Memproses...";

        fetch(baseUrl + '/payment/token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                booking_id: {{ $booking->id }},
                name: "{{ $booking->customer_name }}",
                email: "{{ auth()->user()->email ?? '' }}",
                phone: "{{ auth()->user()->phone ?? '' }}" || ""
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Error response from /payment/token:', err);
                    alert('Error: ' + (err.message || 'Gagal mendapatkan token pembayaran. Silakan coba lagi.'));
                    throw new Error('Error response from /payment/token');
                });
            }
            return response.json();
        })
        .then(data => {
            window.snap.pay(data.snap_token, {
                onSuccess: function(result){
                    alert("Pembayaran berhasil!");
                    window.location.href = "{{ route('booking.receipt', $booking->id) }}";
                },
                onPending: function(result){
                    alert("Pembayaran dalam proses, silakan cek kembali nanti.");
                    window.location.href = "{{ route('booking.receipt', $booking->id) }}";
                },
                onError: function(result){ 
                    alert("Terjadi kesalahan saat proses pembayaran. Silakan coba lagi.");
                    payButton.disabled = false;
                    payButton.textContent = "Bayar Sekarang";
                },
                onClose: function(){ 
                    alert("Popup ditutup tanpa transaksi."); 
                    payButton.disabled = false;
                    payButton.textContent = "Bayar Sekarang";
                }
            });
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert("Gagal mendapatkan token pembayaran. Silakan coba lagi.");
            payButton.disabled = false;
            payButton.textContent = "Bayar Sekarang";
        });
    });
}
</script>

<script>
const baseUrl = "{{ url('') }}";
const bookingId = {{ $booking->id }};
const statusMessage = document.createElement('div');
statusMessage.style.marginTop = '10px';
statusMessage.style.fontWeight = 'bold';

const payButton = document.getElementById("pay-button");
if (payButton) {
    payButton.insertAdjacentElement('afterend', statusMessage);
}

function checkPaymentStatus() {
    statusMessage.textContent = "Checking payment status...";
    fetch(baseUrl + '/booking/payment/status/' + bookingId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                statusMessage.textContent = "Payment status: " + data.payment_status;
                if (data.payment_status === 'paid') {
                    alert("Pembayaran berhasil!");
                    window.location.href = baseUrl + "/receipt/" + bookingId;
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

// Add a "Check Status" button
const checkStatusButton = document.createElement('button');
checkStatusButton.textContent = "Check Status";
checkStatusButton.className = "bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded no-print";
checkStatusButton.style.marginTop = '10px';

if (payButton) {
    payButton.insertAdjacentElement('afterend', checkStatusButton);
    checkStatusButton.addEventListener('click', checkPaymentStatus);
}

setInterval(() => {
    checkPaymentStatus();
}, 30000);
</script>
@endsection
