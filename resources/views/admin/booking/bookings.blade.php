@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h1 class="mb-0">Total Pemesanan Mesin Cuci</h1>
        </div>
        <div class="card-body">

            <form method="GET" action="{{ route('admin.bookings') }}" class="row g-3 mb-4 align-items-center">
                <div class="col-md-5">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari customer atau mesin..." 
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <input 
                        type="date" 
                        name="date" 
                        class="form-control" 
                        value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.bookings') }}" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>

            {{-- Daftar Booking --}}
            @if($bookings->count())
                <ul class="list-group">
                    @foreach ($bookings as $booking)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                <strong>{{ $booking->customer_name }}</strong> - 
                                {{ $booking->machine ? $booking->machine->name : 'Mesin tidak tersedia' }}<br>
                                <small>{{ \Carbon\Carbon::parse($booking->booking_time)->format('d M Y H:i') }}</small>
                            </div>
                            <span class="badge bg-info text-dark mt-1">
                                {{ ucfirst($booking->payment_method) }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $bookings->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="alert alert-info">
                    Tidak ada booking ditemukan.
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
