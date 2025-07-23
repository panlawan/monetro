{{-- resources/views/admin/temp-dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header Card -->
                <div class="card shadow-lg mb-4" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                Admin Dashboard - {{ config('app.name') }}
                            </h4>
                            <div>
                                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">
                                    <i class="fas fa-home me-1"></i>Main App
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light btn-sm">
                                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="{{ auth()->user()->getAvatarUrl() }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="rounded-circle mb-2" 
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <h5>{{ auth()->user()->name }}</h5>
                                <span class="badge bg-{{ auth()->user()->getRoleBadgeColor() }}">
                                    {{ auth()->user()->getRoleDisplayName() }}
                                </span>
                            </div>
                            <div class="col-md-9">
                                <h5>Welcome to Admin Panel!</h5>
                                <p class="text-muted">
                                    You have successfully logged in as an administrator. 
                                    This is a temporary admin dashboard while the full system is being set up.
                                </p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <h6>Your Details:</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Email:</strong> {{ auth()->user()->email }}</li>
                                            <li><strong>Role:</strong> {{ auth()->user()->getRoleDisplayName() }}</li>
                                            <li><strong>Status:</strong> 
                                                <span class="badge bg-{{ auth()->user()->getStatusBadgeColor() }}">
                                                    {{ auth()->user()->getStatusDisplayName() }}
                                                </span>
                                            </li>
                                            <li><strong>Last Login:</strong> {{ auth()->user()->getLastLoginFormatted() ?? 'First time' }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>System Stats:</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Total Users:</strong> {{ \App\Models\User::count() }}</li>
                                            <li><strong>Active Users:</strong> {{ \App\Models\User::where('status', 'active')->count() }}</li>
                                            <li><strong>Admin Users:</strong> {{ \App\Models\User::whereIn('role', ['super_admin', 'admin'])->count() }}</li>
                                            <li><strong>Online Now:</strong> {{ \App\Models\User::where('last_login_at', '>=', now()->subMinutes(15))->count() }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow" style="border-radius: 10px;">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>User Management
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Manage all users in the system.</p>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-success">
                                        <i class="fas fa-list me-2"></i>View All Users
                                    </a>
                                    <button class="btn btn-outline-success" onclick="alert('Feature coming soon!')">
                                        <i class="fas fa-user-plus me-2"></i>Add New User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow" style="border-radius: 10px;">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog me-2"></i>System Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Configure system settings and preferences.</p>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('terms') }}" target="_blank" class="btn btn-info">
                                        <i class="fas fa-file-contract me-2"></i>Terms of Service
                                    </a>
                                    <a href="{{ route('privacy') }}" target="_blank" class="btn btn-outline-info">
                                        <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Message -->
                <div class="card shadow" style="border-radius: 10px;">
                    <div class="card-body text-center">
                        <h5 class="text-primary">🚀 Admin System Status</h5>
                        <p class="text-muted">
                            This is a temporary admin dashboard. The full admin system with complete 
                            user management, statistics, and advanced features is ready to be deployed.
                        </p>
                        <div class="row text-center mt-4">
                            <div class="col-md-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p><small>Authentication</small></p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p><small>User Roles</small></p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <p><small>Full Dashboard</small></p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <p><small>User Management</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple stats refresh
        function refreshStats() {
            location.reload();
        }
        
        // Auto-refresh every 5 minutes
        setInterval(refreshStats, 300000);
    </script>
</body>
</html>