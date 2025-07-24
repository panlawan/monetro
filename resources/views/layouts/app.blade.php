{{-- resources/views/layouts/app.blade.php - Updated --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome for Finance pages -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS & JS via Vite -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Finance-specific styles */
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-danger { border-left: 4px solid #e74a3b !important; }
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .text-gray-800 { color: #5a5c69 !important; }
        .text-gray-300 { color: #dddfeb !important; }
        .shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-coins me-2"></i>{{ config('app.name', 'Laravel') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('finance.dashboard') }}">
                                <i class="fas fa-chart-line me-1"></i>Finance
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    @if (isset($header))
        <header class="bg-white shadow-sm py-4">
            <div class="container">
                <div class="row">
                    <div class="col">
                        {{ $header }}
                    </div>
                </div>
            </div>
        </header>
    @endif

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            {{ $slot }}
        </div>
    </main>
</body>

</html>