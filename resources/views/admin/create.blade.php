@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2>{{ isset($machine) ? 'Edit Machine' : 'Tambah Machine' }}</h2>

    <form action="{{ isset($machine) ? route('machines.update', $machine->id) : route('machines.store') }}" method="POST">
        @csrf
        @if(isset($machine)) @method('PUT') @endif

        <div class="mb-3">
            <label for="name">Nama Machine</label>
            <input type="text" name="name" value="{{ old('name', $machine->name ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="status">Status</label>
            <select name="status" class="form-control" required>
                <option value="" disabled {{ old('status', $machine->status ?? '') == '' ? 'selected' : '' }}>Pilih Status</option>
                <option value="available" {{ old('status', $machine->status ?? '') == 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="booked" {{ old('status', $machine->status ?? '') == 'booked' ? 'selected' : '' }}>Digunakan</option>
                <option value="maintenance" {{ old('status', $machine->status ?? '') == 'maintenance' ? 'selected' : '' }}>Perbaikan</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SweetAlert for Any Validation Error --}}
@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Terjadi Kesalahan!',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        confirmButtonColor: '#d33',
        confirmButtonText: 'OK'
    });
</script>
@endif

{{-- SweetAlert for Success Message --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
</script>
@endif
@endpush
