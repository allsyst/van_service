<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VanService') - VanService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        body { background: #f8f9fc; font-family: 'Nunito', sans-serif; }
        .navbar { background: linear-gradient(90deg, #4e73df, #224abe); }
        .user-sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #e3e6f0; padding-top: 1rem; }
        .user-sidebar .nav-link { color: #5a5c69; padding: 0.75rem 1rem; border-radius: 0.35rem; margin-bottom: 2px; }
        .user-sidebar .nav-link:hover, .user-sidebar .nav-link.active { background: #4e73df; color: #fff; }
        .user-sidebar .nav-link i { width: 20px; }
        .main-content { padding: 1.5rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand font-weight-bold" href="{{ route('user.dashboard') }}">
            <i class="fas fa-shuttle-van mr-2"></i>VanService
        </a>
        <div class="ml-auto d-flex align-items-center">
            <span class="text-white mr-3"><i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</button>
            </form>
        </div>
    </nav>

    <div class="container-fluid px-0">
        <div class="row no-gutters">
            <!-- Sidebar -->
            <div class="col-md-2 user-sidebar">
                <ul class="nav flex-column px-2 mt-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.trips.*') ? 'active' : '' }}" href="{{ route('user.trips.create') }}">
                            <i class="fas fa-plus-circle"></i> Add Trip
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
