@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        #machinesTable td, #machinesTable th {
            padding: 10px;
            font-size: 14px;
            vertical-align: middle;
        }

        #machinesTable th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        #machinesTable .badge {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 8px;
        }

        #machinesTable .btn-sm i {
            margin-right: 4px;
        }

        #machinesTable .d-flex > * {
            margin-right: 8px;
        }

        #machinesTable .d-flex > *:last-child {
            margin-right: 0;
        }
    </style>
@endpush

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
    <div class="d-flex justify-content-between align-items-center w-100">
        <h1 class="mb-0">Daftar Mesin Cuci</h1>
        <a href="{{ route('machines.create') }}" class="btn btn-danger ms-4">Tambah Mesin Cuci</a>
    </div>
</div>


        <div class="card-body">
            @if(session('success'))
                <div id="alert" class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>{{ session('success') }}</strong>
                </div>
            @endif

            {{-- Table --}}
            <table id="machinesTable" class="table table-bordered table-striped table-hover text-center align-middle">
                <thead class="table-light">
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
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('machines.edit', $machine->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Init DataTable
            $('#machinesTable').DataTable({
                pagingType: "simple_numbers",
                language: {
                    search: "Search:",
                    lengthMenu: "_MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "← Previous",
                        next: "Next →"
                    }
                }
            });

            // Auto-hide alert after 3 seconds
            const alert = document.getElementById('alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('show');
                    alert.style.display = 'none';
                }, 3000);
            }

            // SweetAlert for Delete Confirmation
            document.querySelectorAll('.delete-machine-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('.delete-machine-form');
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
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
