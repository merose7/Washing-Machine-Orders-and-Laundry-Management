@extends('adminlte::page')

@section('content')
<div class="container mt-4">
    <h2>Data Mesin Cuci</h2>
    <a href="{{ route('machines.create') }}" class="btn btn-primary mb-3">Tambah Mesin Cuci</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($machines as $machine)
                <tr>
                    <td>{{ $machine->name }}</td>
                    <td>
                        @if($machine->status == 'available')
                            <span class="badge bg-success">Tersedia</span>
                        @elseif($machine->status == 'booked')
                            <span class="badge bg-warning text-dark">Digunakan</span>
                        @else
                            <span class="badge bg-danger">Perbaikan</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('machines.destroy', $machine->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
