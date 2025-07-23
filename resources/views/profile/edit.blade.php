{{-- resources/views/profile/edit.blade.php (Fixed Bootstrap Version) --}}

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-weight-bold text-gray-800 mb-0">
                <i class="fas fa-user-edit me-2"></i>Profile Settings
            </h2>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container">

            <!-- Profile Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}"
                                        class="rounded-circle border border-3 border-white"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 fw-bold">{{ $user->name }}</h3>
                                    <p class="mb-2 opacity-75">{{ $user->email }}</p>
                                    <div class="d-flex gap-2">
                                        <!-- Role Badge -->
                                        <span class="badge 
                                            @if($user->role == 'super_admin') bg-danger
                                            @elseif($user->role == 'admin') bg-warning
                                            @elseif($user->role == 'moderator') bg-info
                                            @else bg-success @endif">
                                            <i class="fas 
                                                @if($user->role == 'super_admin') fa-crown
                                                @elseif($user->role == 'admin') fa-shield-alt
                                                @elseif($user->role == 'moderator') fa-user-shield
                                                @else fa-user @endif me-1"></i>
                                            {{ $user->getRoleDisplayName() }}
                                        </span>

                                        <!-- Status Badge -->
                                        <span class="badge 
                                            @if($user->status == 'active') bg-success
                                            @elseif($user->status == 'inactive') bg-secondary
                                            @elseif($user->status == 'suspended') bg-danger
                                            @else bg-warning @endif">
                                            <i class="fas 
                                                @if($user->status == 'active') fa-check-circle
                                                @elseif($user->status == 'inactive') fa-minus-circle
                                                @elseif($user->status == 'suspended') fa-ban
                                                @else fa-clock @endif me-1"></i>
                                            {{ $user->getStatusDisplayName() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-auto text-center">
                                    <h4 class="mb-0">{{ number_format($user->login_count) }}</h4>
                                    <small class="opacity-75">Total Logins</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">

                    <!-- Profile Information -->
                    <div class="card shadow mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-user-circle me-2"></i>Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user me-2 text-primary"></i>Full Name
                                        </label>
                                        <input id="name" name="name" type="text" class="form-control form-control-lg"
                                            value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="text-danger mt-1">
                                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                        </label>
                                        <input id="email" name="email" type="email" class="form-control form-control-lg"
                                            value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="text-danger mt-1">
                                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone me-2 text-primary"></i>Phone Number
                                        </label>
                                        <input id="phone" name="phone" type="tel" class="form-control form-control-lg"
                                            value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                                        @error('phone')
                                            <div class="text-danger mt-1">
                                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="timezone" class="form-label">
                                            <i class="fas fa-globe me-2 text-primary"></i>Timezone
                                        </label>
                                        <select id="timezone" name="timezone" class="form-select form-select-lg">
                                            <option value="">Select Timezone</option>
                                            <option value="Asia/Bangkok" {{ ($user->timezone ?? '') == 'Asia/Bangkok' ? 'selected' : '' }}>🇹🇭 Asia/Bangkok</option>
                                            <option value="UTC" {{ ($user->timezone ?? '') == 'UTC' ? 'selected' : '' }}>
                                                🌍 UTC</option>
                                            <option value="America/New_York" {{ ($user->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>🇺🇸 America/New_York
                                            </option>
                                            <option value="Europe/London" {{ ($user->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>🇬🇧 Europe/London</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    @if (session('status') === 'profile-updated')
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                                        </div>
                                    @else
                                        <div></div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Password Update -->
                    <div class="card shadow mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-lock me-2"></i>Security Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('password.update') }}">
                                @csrf
                                @method('put')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-key me-2 text-danger"></i>Current Password
                                    </label>
                                    <input id="current_password" name="current_password" type="password"
                                        class="form-control form-control-lg" autocomplete="current-password">
                                    @error('current_password')
                                        <div class="text-danger mt-1">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2 text-danger"></i>New Password
                                        </label>
                                        <input id="password" name="password" type="password"
                                            class="form-control form-control-lg" autocomplete="new-password">
                                        @error('password')
                                            <div class="text-danger mt-1">
                                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">
                                            <i class="fas fa-check-double me-2 text-danger"></i>Confirm Password
                                        </label>
                                        <input id="password_confirmation" name="password_confirmation" type="password"
                                            class="form-control form-control-lg" autocomplete="new-password">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    @if (session('status') === 'password-updated')
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle me-2"></i>Password updated successfully!
                                        </div>
                                    @else
                                        <div></div>
                                    @endif

                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-shield-alt me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">

                    <!-- Account Overview -->
                    <div class="card shadow mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Account Overview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <!-- <h3 class="text-primary mb-0">{{ $user->created_at->diffInDays() }}</h3> -->
                                    <h3 class="text-primary mb-0">
                                        {{ number_format($user->created_at->diffInSeconds() / 86400, 2) }}
                                    </h3>
                                    <small class="text-muted">Days as Member</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <h3 class="text-success mb-0">{{ number_format($user->login_count) }}</h3>
                                    <small class="text-muted">Total Logins</small>
                                </div>
                            </div>

                            <hr>

                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Member Since</span>
                                    <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                                </div>

                                @if($user->last_login_at)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>Last Login</span>
                                        <strong>{{ $user->getLastLoginFormatted() }}</strong>
                                    </div>
                                @endif

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Email Status</span>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Verified
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Unverified
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legal Compliance -->
                    <div class="card shadow mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-gavel me-2"></i>Legal Compliance
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Terms of Service</span>
                                    @if($user->hasAcceptedTerms())
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Accepted
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @endif
                                </div>

                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>Privacy Policy</span>
                                    @if($user->hasAcceptedPrivacy())
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Accepted
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body d-grid gap-2">
                            @if($user->canAccessAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                    <i class="fas fa-shield-alt me-2"></i>Admin Panel
                                </a>
                            @endif

                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print Profile
                            </button>

                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('⚠️ Are you sure you want to delete your account?\n\nThis action cannot be undone!')) {
                if (confirm('🚨 FINAL WARNING: This will PERMANENTLY delete ALL your data.\n\nClick OK to confirm deletion.')) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("profile.destroy") }}';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        // Add smooth transitions
        document.addEventListener('DOMContentLoaded', function () {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'transform 0.3s ease';
                });

                card.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Add form validation feedback
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.style.borderColor = '#007bff';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(0, 123, 255, 0.25)';
                });
            });
        });
    </script>
</x-app-layout>