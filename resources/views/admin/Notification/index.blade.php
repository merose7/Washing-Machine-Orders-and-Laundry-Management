@extends('layouts.admin') 
@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Notifikasi</h1>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Cari pesan notifikasi..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

    {{-- Daftar Notifikasi --}}
    <div class="card shadow-sm">
        <div class="card-body">
            @if($notifications->count())
                <ul class="list-group">
                    @foreach ($notifications as $notification)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $notification->message ?? 'Isi notifikasi' }}
                            <span class="badge bg-light text-muted">{{ $notification->created_at->format('d M Y H:i') }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $notifications->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">Tidak ada notifikasi ditemukan.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Bisa ditambah custom JavaScript jika perlu
    });
</script>
@endsection
