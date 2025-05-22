@extends('layouts.guest')

@section('title', 'Booking Mesin Cuci')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
@endpush

@section('content')
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4">Booking Mesin Cuci</h2>

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

        <!-- Removed unused name input field as customer_name is taken from Auth user -->

        <div class="row">
            <div class="col-md-6 mb-3">
            <label for="booking_date" class="form-label">Tanggal Booking</label>
            <input type="date" class="form-control" id="booking_date" name="booking_date" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="booking_time" class="form-label">Waktu Booking</label>
            <input type="time" class="form-control" id="booking_time" name="booking_time" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="booking_duration" class="form-label">Durasi Booking (menit)</label>
            <input type="number" class="form-control" id="booking_duration" name="booking_duration" min="1" required>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('booking_date');
    const timeInput = document.getElementById('booking_time');
    const now = new Date();

    // Helper padding function
    const pad = (n) => n.toString().padStart(2, '0');

    // Set minimum date to today
    const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
    dateInput.min = todayStr;

    // Update time input min/max based on selected date
    const updateTimeLimits = () => {
        const selectedDate = new Date(dateInput.value);
        const isToday = selectedDate.toDateString() === now.toDateString();

        if (isToday) {
            // If today, restrict time from current time to 23:59
            const minTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
            timeInput.min = minTime;
            timeInput.max = '23:59';
        } else {
            // Any future date, allow full day
            timeInput.min = '00:00';
            timeInput.max = '23:59';
        }
    };

    dateInput.addEventListener('change', updateTimeLimits);
    
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

        <button type="submit" class="btn btn-danger w-100">Booking Sekarang</button>
        <div class="text-center mt-3">
        <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">
        Kembali
        </a>
        </div>

</div>
@endsection
