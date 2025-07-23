{{-- resources/views/admin/temp-users.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management - {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body style="background: #f8f9fa; min-height: 100vh;">
    
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-users me-2 text-primary"></i>
                User Management
            </h2>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-home me-1"></i>Main App
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h3 class="text-primary">{{ $users->total() }}</h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h3 class="text-success">{{ \App\Models\User::where('status', 'active')->count() }}</h3>
                        <p class="mb-0">Active</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h3 class="text-warning">{{ \App\Models\User::whereIn('role', ['super_admin', 'admin'])->count() }}</h3>
                        <p class="mb-0">Admins</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h3 class="text-info">{{ \App\Models\User::where('last_login_at', '>=', now()->subMinutes(15))->count() }}</h3>
                        <p class="mb-0">Online</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>All Users
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->getAvatarUrl() }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle me-2" 
                                                 style="width: 40px; height: 40px;">
                                            <div>
                                                <div class="fw-semibold">{{ $user->name }}</div>
                                                <small class="text-muted">ID: {{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-{{ $user->getRoleBadgeColor() }}">
                                            {{ $user->getRoleDisplayName() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->getStatusBadgeColor() }}">
                                            {{ $user->getStatusDisplayName() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->last_login_at)
                                            <span title="{{ $user->last_login_at->format('Y-m-d H:i:s') }}">
                                                {{ $user->getLastLoginFormatted() }}
                                            </span>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewUser({{ $user->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(auth()->user()->canEdit($user))
                                                <button class="btn btn-outline-warning" onclick="editUser({{ $user->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                            @if(auth()->user()->canDelete($user))
                                                <button class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No users found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function viewUser(userId) {
            alert('View User #' + userId + '\n\nThis feature will be available in the full admin system.');
        }

        function editUser(userId) {
            alert('Edit User #' + userId + '\n\nThis feature will be available in the full admin system.');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                alert('Delete User #' + userId + '\n\nThis feature will be available in the full admin system.');
            }
        }
    </script>
</body>
</html>