
<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="info">
        <a  href="{{ route('profile.edit') }}" class="d-block">Admin</a>
    </div>
</div>

<!-- Search Form -->
<div class="form-inline">
    <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
            <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
            </button>
        </div>
    </div>
</div>

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt" style="color:#17a2b8;"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('admin/machines') }}" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>Mesin Cuci</p>
            </a>
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
                <p>Total Pembayaran</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('admin/notifications') }}" class="nav-link">
                <i class="nav-icon fas fa-bell"></i>
                <p>Notifikasi</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('profile.edit') }}" class="nav-link">
                <i class="nav-icon fas fa-user"></i>
                <p>Profil Admin</p>
            </a>
        </li>
    </ul>
</nav>
