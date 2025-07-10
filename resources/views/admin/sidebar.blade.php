<!--<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="info">
       <a  href="{{ route('profile.edit') }}" class="d-block">Admin</a> 
    </div>
</div> -->

<!-- Search Form -->
<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
            <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
            </button>
        </div>
    </div>
</div>

<div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
    <div class="info">
        <span class="d-block text-white font-weight-medium">Menu</span>
    </div>
</div>

<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-tshirt"></i>
                <p>
                    Mesin Cuci
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
                <li class="nav-item">
                    <a href="{{url('admin/machines')}}" class="nav-link" id="toggleMachinesList">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Daftar Mesin</p>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ url('admin/bookings') }}" class="nav-link">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>Total Booking</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('admin/payments') }}" class="nav-link">
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Detail Pemasukan</p>
            </a>
        </li>
       <!-- <li class="nav-item">
            <a href="{{ url('admin/finance-report') }}" class="nav-link">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Laporan Keuangan</p>
            </a>
        </li> -->
        <li class="nav-item">
            <a href="{{ url('admin/notifications') }}" class="nav-link">
                <i class="nav-icon fas fa-envelope"></i>
                <p>Notifikasi Gmail </p>
            </a>
        </li>

<!-- Menu Admin -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="info">
        <a  href="" class="d-block"> </a>
    </div>
</div>
        <li class="nav-item">
            <a href="{{ route('profile.edit') }}" class="nav-link">
                <i class="nav-icon fas fa-user"></i>
                <p>Profil Admin</p>
            </a>
        </li>
        <li class="nav-item">
        <a href="{{ route('logout') }}" class="nav-link"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="nav-icon fas fa-sign-out-alt"></i>
        <p>Log Out</p>
    </a>
</li>
    </ul>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleLink = document.getElementById('toggleMachinesList');
    const machinesListContainer = document.getElementById('machinesListContainer');

    if (toggleLink && machinesListContainer) {
        toggleLink.addEventListener('click', function (e) {
            e.preventDefault();
            if (machinesListContainer.style.display === 'none' || machinesListContainer.style.display === '') {
                machinesListContainer.style.display = 'block';
            } else {
                machinesListContainer.style.display = 'none';
            }
        });
    }
});
</script>
