{{-- resources/views/layouts/admin.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        .admin-layout {
            background: var(--monetro-gray-100);
            min-height: 100vh;
        }
        
        .admin-navbar {
            background: linear-gradient(135deg, var(--monetro-brand-primary) 0%, var(--monetro-brand-secondary) 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-sidebar {
            background: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 4px rgba(0,0,0,0.05);
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        .sidebar-nav .nav-link {
            color: var(--monetro-gray-700);
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: var(--monetro-brand-primary);
            color: white;
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
        }
        
        .admin-user-menu .dropdown-toggle::after {
            display: none;
        }
        
        .impersonation-banner {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            text-align: center;
            padding: 0.5rem;
            font-weight: 600;
        }
    </style>
</head>

<body class="admin-layout">
    <!-- Impersonation Banner -->
    @if(session('impersonating'))
        <div class="impersonation-banner">
            <i class="fas fa-user-secret me-2"></i>
            You are impersonating {{ auth()->user()->name }}
            <form action="{{ route('stop-impersonating') }}" method="POST" class="d-inline ms-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i>Stop Impersonating
                </button>
            </form>
        </div>
    @endif

    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand fw-bold text-white" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-shield-alt me-2"></i>
                {{ config('app.name') }} Admin
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation items -->
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                    </li>
                </ul>

                <!-- User menu -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown admin-user-menu">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->getAvatarUrl() }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="rounded-circle me-2" 
                                 style="width: 32px; height: 32px;">
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <x-role-badge :user="auth()->user()" />
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="fas fa-user me-2"></i>{{ auth()->user()->getDisplayName() }}
                                </h6>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="fas fa-home me-2"></i>Main App
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-2"></i>Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="sidebar-nav">
                    <nav class="nav flex-column">
                        <!-- Dashboard -->
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                           href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>

                        <!-- User Management -->
                        <div class="nav-section mt-3">
                            <h6 class="px-3 text-muted text-uppercase small fw-bold">User Management</h6>
                            
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
                               href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i>All Users
                            </a>
                            
                            <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'admin']) }}">
                                <i class="fas fa-user-shield"></i>Admins
                            </a>
                            
                            <a class="nav-link" href="{{ route('admin.users.index', ['status' => 'pending']) }}">
                                <i class="fas fa-clock"></i>Pending Users
                                @php
                                    $pendingCount = \App\Models\User::where('status', 'pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="badge bg-warning ms-auto">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            
                            <a class="nav-link" href="{{ route('admin.users.index', ['status' => 'suspended']) }}">
                                <i class="fas fa-ban"></i>Suspended
                            </a>
                        </div>

                        <!-- System -->
                        <div class="nav-section mt-3">
                            <h6 class="px-3 text-muted text-uppercase small fw-bold">System</h6>
                            
                            <a class="nav-link" href="#" onclick="refreshStats()">
                                <i class="fas fa-sync-alt"></i>Refresh Stats
                            </a>
                            
                            <a class="nav-link" href="{{ route('terms') }}" target="_blank">
                                <i class="fas fa-file-contract"></i>Terms of Service
                            </a>
                            
                            <a class="nav-link" href="{{ route('privacy') }}" target="_blank">
                                <i class="fas fa-shield-alt"></i>Privacy Policy
                            </a>
                        </div>

                        <!-- Quick Stats -->
                        <div class="nav-section mt-4">
                            <h6 class="px-3 text-muted text-uppercase small fw-bold">Quick Stats</h6>
                            <div class="px-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Total Users</small>
                                    <span class="badge bg-primary">{{ \App\Models\User::count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Online Now</small>
                                    <span class="badge bg-success">{{ \App\Models\User::online()->count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Admins</small>
                                    <span class="badge bg-warning">{{ \App\Models\User::admins()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <main class="admin-content">
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Page Content -->
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-refresh quick stats every 2 minutes
        function refreshStats() {
            location.reload();
        }
        
        setInterval(refreshStats, 120000);

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // AJAX Setup
        window.ajaxSetup = function() {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            };
        };

        // Global error handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
        });
    </script>

    @stack('scripts')
</body>
</html>