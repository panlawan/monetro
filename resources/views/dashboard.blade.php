<x-app-layout>
    <x-slot name="header">
        <h2 class="h3 mb-0 text-dark">Dashboard</h2>
    </x-slot>

    <!-- Email Verification Status -->
    @if (auth()->user()->hasVerifiedEmail())
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>Your email is verified!</div>
        </div>
    @else
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                Please verify your email address.
                <a href="{{ route('verification.notice') }}" class="alert-link ms-2">Click here to verify</a>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <div class="stat-value">{{ auth()->user()->name }}</div>
                    <div class="stat-label">Welcome to Dashboard
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">Email Status</h5>
                    @if(auth()->user()->hasVerifiedEmail())
                        <span class="badge bg-success fs-6">Verified</span>
                    @else
                        <span class="badge bg-warning fs-6">Not Verified</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card text-center">
                <div class="card-body">
                    <h5 class="card-title">Member Since</h5>
                    <p class="card-text">{{ auth()->user()->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Dashboard Overview</h5>
                </div>
                <div class="card-body">
                    <h6>Welcome to your dashboard!</h6>
                    <p>Here you can manage your account and access various features:</p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            View your account information
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Update your profile settings
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Manage your preferences
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Access application features
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                            <i class="bi bi-person me-2"></i>Edit Profile
                        </a>
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-gear me-2"></i>Settings
                        </button>
                        <button class="btn btn-outline-info">
                            <i class="bi bi-question-circle me-2"></i>Help & Support
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-1 text-muted">Name:</p>
                            <p class="mb-3">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-1 text-muted">Email:</p>
                            <p class="mb-3">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->phone)
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted">Phone:</p>
                                <p class="mb-3">{{ auth()->user()->phone }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>