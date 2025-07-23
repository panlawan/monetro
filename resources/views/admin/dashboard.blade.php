{{-- resources/views/admin/dashboard.blade.php --}}

@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Admin Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
        </h1>
        <div class="d-flex">
            @if(session('impersonating'))
                <form action="{{ route('stop-impersonating') }}" method="POST" class="me-2">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-user-times me-1"></i>Stop Impersonating
                    </button>
                </form>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-home me-1"></i>Back to App
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Admin Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['admin_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Online Now</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['online_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Data -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus me-2"></i>Recent Users
                    </h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @forelse($recent_users as $user)
                        <div class="d-flex align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" 
                                 class="rounded-circle me-3" style="width: 40px; height: 40px;">
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            <div class="text-end">
                                <x-role-badge :user="$user" />
                                <br>
                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted">No recent users</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Online Users -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-globe me-2"></i>Currently Online
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($online_users as $user)
                        <div class="d-flex align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="position-relative me-3">
                                <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" 
                                     class="rounded-circle" style="width: 35px; height: 35px;">
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                    <span class="visually-hidden">Online</span>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->getLastLoginFormatted() }}</small>
                            </div>
                            <x-role-badge :user="$user" />
                        </div>
                    @empty
                        <p class="text-center text-muted">No users currently online</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row">
        <!-- System Statistics -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>System Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">User Verification</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Verified Users</span>
                                    <span class="fw-bold">{{ $stats['verified_users'] }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['total_users'] > 0 ? ($stats['verified_users'] / $stats['total_users']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            
                            <h6 class="text-primary">Legal Acceptance</h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Accepted Legal Docs</span>
                                    <span class="fw-bold">{{ $stats['legal_accepted'] }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" style="width: {{ $stats['total_users'] > 0 ? ($stats['legal_accepted'] / $stats['total_users']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">User Activity</h6>
                            <ul class="list-unstyled">
                                <li class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-clock me-2 text-muted"></i>Online Now</span>
                                    <span class="badge bg-success">{{ $stats['online_users'] }}</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-calendar-day me-2 text-muted"></i>Logged Today</span>
                                    <span class="badge bg-primary">{{ \App\Models\User::where('last_login_at', '>=', now()->startOfDay())->count() }}</span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span><i class="fas fa-calendar-week me-2 text-muted"></i>This Week</span>
                                    <span class="badge bg-info">{{ \App\Models\User::where('last_login_at', '>=', now()->startOfWeek())->count() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                        
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-user-plus me-2"></i>Create New User
                        </button>
                        
                        <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="btn btn-warning">
                            <i class="fas fa-clock me-2"></i>Pending Users
                        </a>
                        
                        <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="btn btn-danger">
                            <i class="fas fa-ban me-2"></i>Suspended Users
                        </a>
                        
                        <button type="button" class="btn btn-info" onclick="refreshStats()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Stats
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Create New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    @foreach(\App\Models\User::getAllRoles() as $roleKey => $roleName)
                                        @if(auth()->user()->isSuperAdmin() || !in_array($roleKey, ['super_admin', 'admin']))
                                            <option value="{{ $roleKey }}">{{ $roleName }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone (Optional)</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Create User Form
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.users.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User created successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the user.');
    });
});

// Refresh Statistics
function refreshStats() {
    fetch('{{ route("admin.api.statistics") }}', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update statistics on page
        console.log('Statistics updated:', data);
        location.reload(); // Simple reload for now
    })
    .catch(error => {
        console.error('Error refreshing stats:', error);
    });
}

// Auto-refresh every 5 minutes
setInterval(refreshStats, 300000);
</script>
@endsection