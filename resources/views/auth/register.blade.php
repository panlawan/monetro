{{-- resources/views/auth/register.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="auth-layout">
    <div class="container">
        <div class="auth-card register-card">
            <div class="auth-header">
                <i class="fas fa-user-plus fa-3x mb-3"></i>
                <h2>{{ config('app.name', 'Monetro.io') }}</h2>
                <p class="mb-0">Create Your Account</p>
            </div>
            
            <div class="auth-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Registration failed!</strong> Please check the form below.
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <!-- Full Name -->
                    <div class="floating-label">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus
                                   placeholder=" ">
                            <label for="name">Full Name</label>
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="floating-label">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
                                   placeholder=" ">
                            <label for="email">Email Address</label>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone Number (Optional) -->
                    <div class="floating-label">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   placeholder=" ">
                            <label for="phone">Phone Number (Optional)</label>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="floating-label">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder=" ">
                            <label for="password">Password</label>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="strength-meter" id="strengthMeter"></div>
                        <small class="text-muted" id="strengthText">Password strength will appear here</small>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="floating-label">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   placeholder=" ">
                            <label for="password_confirmation">Confirm Password</label>
                        </div>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('terms') is-invalid @enderror" 
                               type="checkbox" 
                               id="terms" 
                               name="terms" 
                               required
                               {{ old('terms') ? 'checked' : '' }}>
                        <label class="form-check-label" for="terms">
                            I agree to the 
                            <a href="#" class="text-decoration-none terms-link" data-bs-toggle="modal" data-bs-target="#legalModal" data-type="terms">Terms of Service</a>
                            and 
                            <a href="#" class="text-decoration-none privacy-link" data-bs-toggle="modal" data-bs-target="#legalModal" data-type="privacy">Privacy Policy</a>
                        </label>
                        @error('terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Register Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-auth btn-lg" id="registerBtn">
                            <i class="fas fa-user-plus me-2"></i>
                            <span class="btn-text">Create Account</span>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="auth-link">
                        <span class="text-muted">Already have an account?</span>
                        <a href="{{ route('login') }}" class="text-decoration-none ms-1">
                            <i class="fas fa-sign-in-alt me-1"></i>Sign in here
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Legal Documents Modal -->
    @include('components.legal-modal')

    <!-- JavaScript for Modal functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF Token setup for AJAX
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Handle modal trigger
            document.addEventListener('click', function(e) {
                if (e.target.matches('.terms-link, .privacy-link')) {
                    e.preventDefault();
                    const type = e.target.getAttribute('data-type');
                    loadLegalContent(type);
                }
            });

            // Load legal content via AJAX
            function loadLegalContent(type) {
                const modal = document.getElementById('legalModal');
                const modalTitle = modal.querySelector('.modal-title');
                const modalBody = modal.querySelector('.modal-body');
                const acceptBtn = modal.querySelector('#acceptLegalBtn');
                
                // Show loading state
                modalTitle.textContent = 'Loading...';
                modalBody.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading content...</p></div>';
                
                // Set accept button data
                acceptBtn.setAttribute('data-type', type);
                
                // Fetch content
                fetch(`/api/legal/${type}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modalTitle.textContent = data.title;
                    modalBody.innerHTML = renderLegalContent(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<div class="alert alert-danger">Error loading content. Please try again.</div>';
                });
            }

            // Render legal content
            function renderLegalContent(data) {
                let html = `
                    <div class="legal-header-modal mb-4">
                        <h5 class="text-primary">${data.title}</h5>
                        <p class="text-muted mb-0">Last updated: ${data.lastUpdated}</p>
                    </div>
                    <div class="legal-content-modal">
                `;
                
                data.sections.forEach(section => {
                    html += `
                        <div class="legal-section-modal mb-3">
                            <h6 class="text-dark fw-bold">${section.title}</h6>
                            <div class="text-muted">${section.content}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
                return html;
            }

            // Handle accept button
            document.getElementById('acceptLegalBtn').addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const termsCheckbox = document.getElementById('terms');
                
                // Check the terms checkbox
                termsCheckbox.checked = true;
                
                // Record acceptance if user is authenticated
                @auth
                fetch('/api/legal/accept', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        document_type: type,
                        accepted: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Acceptance recorded:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error recording acceptance:', error);
                });
                @endauth
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('legalModal'));
                modal.hide();
            });

            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    
                    if (type === 'password') {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    } else {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    }
                });
            }

            // Password strength meter
            const passwordInput = document.getElementById('password');
            const strengthMeter = document.getElementById('strengthMeter');
            const strengthText = document.getElementById('strengthText');

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strength = checkPasswordStrength(password);
                    
                    strengthMeter.className = 'strength-meter';
                    strengthMeter.classList.add(`strength-${strength.level}`);
                    strengthText.textContent = strength.text;
                });
            }

            function checkPasswordStrength(password) {
                if (password.length < 6) {
                    return { level: 'weak', text: 'Password too short' };
                }
                
                let score = 0;
                if (password.length >= 8) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;
                
                if (score < 3) {
                    return { level: 'weak', text: 'Weak password' };
                } else if (score < 4) {
                    return { level: 'medium', text: 'Medium strength' };
                } else {
                    return { level: 'strong', text: 'Strong password' };
                }
            }
        });
    </script>
</body>
</html>