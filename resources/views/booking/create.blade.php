@extends('layouts.guest')

@section('title', 'Booking Mesin Cuci')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@section('content')
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Pemesanan Mesin Cuci</h2>

    <form action="/booking" method="POST" class="p-4 shadow rounded bg-white">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input type="hidden" name="machine_id" value="{{ $machineId }}">

        <div class="row">
            <div class="col-md-6 mb-3">
            <label for="booking_date" class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="booking_date" name="booking_date" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="booking_time" class="form-label">Waktu</label>
            <input type="time" class="form-control" id="booking_time" name="booking_time" required>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('booking_date');
    const timeInput = document.getElementById('booking_time');
    const form = document.getElementById('bookingForm');

    // Helper padding function
    const pad = (n) => n.toString().padStart(2, '0');

    // Set minimum and maximum date to today to disable future dates
    const now = new Date();
    const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    dateInput.min = todayStr;
    dateInput.max = todayStr;

    const updateTimeLimits = () => {
        const now = new Date();
        const selectedDate = new Date(dateInput.value);
        const isToday = selectedDate.toDateString() === now.toDateString();

        if (isToday) {
            const minTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
            timeInput.min = minTime;
            timeInput.max = '22:00';

            if (minTime > '22:00') {
                timeInput.disabled = true;
            } else {
                timeInput.disabled = false;
            }
        } else {
            timeInput.min = '07:00';
            timeInput.max = '22:00';
            timeInput.disabled = false;
        }
    };

    dateInput.addEventListener('change', updateTimeLimits);

    // Validate time on input to prevent selecting past time if date is today
    timeInput.addEventListener('input', () => {
        const now = new Date();
        const selectedDate = new Date(dateInput.value);
        const isToday = selectedDate.toDateString() === now.toDateString();

        if (isToday) {
            const [hours, minutes] = timeInput.value.split(':').map(Number);
            if (hours < now.getHours() || (hours === now.getHours() && minutes < now.getMinutes())) {
                timeInput.value = timeInput.min;
            }
        }
    });

    // Validate on form submit
    form.addEventListener('submit', (e) => {
        const now = new Date();
        const selectedDate = new Date(dateInput.value);
        const isToday = selectedDate.toDateString() === now.toDateString();

        if (isToday) {
            const [hours, minutes] = timeInput.value.split(':').map(Number);
            if (hours < now.getHours() || (hours === now.getHours() && minutes < now.getMinutes())) {
                e.preventDefault();
                alert('Waktu yang dipilih sudah lewat. Silakan pilih waktu yang valid.');
                timeInput.focus();
            }
        }
    });

    // Initialize time limit on load if date is pre-filled
    if (dateInput.value) {
        updateTimeLimits();
    }
});
</script>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Metode Pembayaran</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="" disabled selected>Pilih metode pembayaran</option>
                <option value="cash">Cash</option>
                <option value="midtrans">Midtrans</option>
            </select>
        </div>

        <button type="submit" class="btn btn-danger w-100">Pesan Sekarang</button>
        <div class="text-center mt-3">
        <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">
        Kembali
        </a>
        </div>

</div>
@endsection
