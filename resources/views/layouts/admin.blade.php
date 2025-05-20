<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel')</title>

    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">

        <!-- Left Navbar -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ url('dashboard.admin') }}" class="nav-link">Home</a>
            </li>
        </ul>

        @php
            use App\Models\Notification;
            $cashNotifications = Notification::where('payment_method', 'cash')->where('is_read', false)->latest()->get();
            $notifications = Notification::where('payment_method', 'gmail')->where('is_read', false)->latest()->get();
        @endphp

        <!-- Right Navbar -->
        <ul class="navbar-nav ml-auto">

            {{-- Cash Notification --}}
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    @if($cashNotifications->count() > 0)
                        <span class="badge badge-warning navbar-badge">{{ $cashNotifications->count() }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">{{ $cashNotifications->count() }} Notifikasi Pembayaran Cash</span>
                    @forelse ($cashNotifications as $notification)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('admin.notifications.cash') }}" class="dropdown-item">
                            <i class="fas fa-money-bill-wave text-success mr-2"></i>
                            {{ \Illuminate\Support\Str::limit($notification->message, 50) }}
                            <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
                        </a>
                    @empty
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item text-center text-muted">Tidak ada Notifikasi Pembayaran Cash</span>
                    @endforelse
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.notifications.cash') }}" class="dropdown-item dropdown-footer">
                        Lihat Semua Notifikasi Pembayaran Cash
                    </a>
                </div>
            </li>

            {{-- Fullscreen Toggle --}}
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>

            {{-- User Dropdown --}}
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button">
                    Admin
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>

        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ url('/') }}" class="brand-link d-flex align-items-center">
            <img src="{{ asset('images/Logo_The_Daily_Wash-removebg-preview.png') }}"
                 alt="The DailyWash"
                 class="brand-image"
                 style="max-height: 55px; width: auto;">
        </a>
        <div class="sidebar">
            @include('admin.sidebar')
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">

        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                @yield('content_header')
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer text-center">
        <strong>&copy; {{ date('Y') }} <a href="{{ url('/') }}">The DailyWash</a></strong> - All rights reserved.
    </footer>

</div>

<!-- Scripts -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')

</body>
</html>
