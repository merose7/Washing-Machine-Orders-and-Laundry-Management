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

        <input type="hidden" name="machine_id" value="{{ $machineId }}">

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="time" class="form-label">Waktu Booking</label>
            <input type="datetime-local" name="time" id="time" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="payment" class="form-label">Metode Pembayaran</label>
            <select name="payment" id="payment" class="form-select" required>
                <option value="" disabled selected>Pilih metode pembayaran</option>
                <option value="cash">Cash</option>
                <option value="midtrans">Midtrans</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Booking Sekarang</button>
    </form>
</div>
@endsection
