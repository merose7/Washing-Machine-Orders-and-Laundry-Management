@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2>{{ isset($machine) ? 'Edit Machine' : 'Tambah Machine' }}</h2>

    <form action="{{ isset($machine) ? route('machines.update', $machine->id) : route('machines.store') }}" method="POST">
        @csrf
        @if(isset($machine)) @method('PUT') @endif

        <div class="mb-3">
            <label for="name">Nama Mesin Cuci</label>
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

    @if(isset($machine))
    <form id="delete-machine-form" action="{{ route('machines.destroy', $machine->id) }}" method="POST" class="mt-3">
        @csrf
        @method('DELETE')
        <button type="button" id="delete-machine-btn" class="btn btn-danger">Hapus Mesin</button>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SweetAlert for Validation Errors --}}
@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan Data!',
        html: `{!! implode('<br>', $errors->all()) !!}`,
        confirmButtonColor: '#d33',
        confirmButtonText: 'OK'
    });
</script>
@endif

{{-- SweetAlert for Success --}}
@if (session('success'))
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

{{-- SweetAlert for Delete Confirmation --}}
<script>
document.getElementById('delete-machine-btn')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Hapus Mesin?',
        text: "Apakah Anda yakin ingin menghapus mesin ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-machine-form').submit();
        }
    });
});
</script>
@endpush


