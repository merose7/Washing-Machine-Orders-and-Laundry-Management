@extends('layouts.admin')

@push('styles')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h1 class="mb-0">Data Mesin Cuci</h1>
        <div class="text-right mb-3">
            <a href="{{ route('machines.create') }}" class="btn btn-danger mb-3">Tambah Mesin Cuci</a>
        </div>
    </div>

<!-- Alert Success -->
    @if(session('success'))
    <div id="alert" class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>{{ session('success') }}</strong>
    </div>
@endif
@push('scripts')
<script>
    setTimeout(() => {
        const alert = document.getElementById('alert');
        if (alert) {
            alert.classList.remove('show');
            alert.classList.add('fade');
            alert.style.display = 'none';
        }
    }, 1500); //1,5 detik
</script>
@endpush

<!-- Table Mesin -->
   <table id="machinesTable" class="table table-bordered table-striped text-center align-middle">
    <thead class="thead-light">
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
                        @if($machine->status === 'available')
                            <span class="badge bg-success">Tersedia</span>
                        @elseif($machine->status === 'booked')
                            <span class="badge bg-warning text-dark">Digunakan</span>
                        @else
                            <span class="badge bg-danger">Perbaikan</span>
                        @endif
                    </td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-sm btn-warning me-2" style="margin-right: 10px;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('machines.destroy', $machine->id) }}" method="POST" onsubmit="return confirm('Yakin ingin hapus?')" class="ms-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</div>
@endsection

@push('scripts')
    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Init DataTable
            $('#machinesTable').DataTable({
                 language: {
                    search: "Search:",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "← Previous",
                        next: "Next →"
                    }
                }
            });

            // Auto-hide alert success
            const alert = document.getElementById('alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.style.display = 'none';
                }, 3000);
            }
        });
    </script>
@endpush
