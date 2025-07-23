{{-- resources/views/legal/terms.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service - {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="legal-layout">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="fas fa-chart-line me-2 text-primary"></i>{{ config('app.name', 'Monetro.io') }}
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a class="nav-link" href="{{ route('register') }}">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <!-- Header -->
                <div class="legal-header text-center">
                    <h1 class="legal-title">Terms of Service</h1>
                    <p class="legal-subtitle">Last updated: {{ now()->format('F d, Y') }}</p>
                </div>

                <!-- Terms Content -->
                <div class="legal-content">
                    <!-- Introduction -->
                    <section class="legal-section">
                        <h2>1. Introduction</h2>
                        <p>Welcome to {{ config('app.name') }} ("we," "our," or "us"). These Terms of Service ("Terms") govern your use of our personal finance management platform and services (the "Service") operated by {{ config('app.name') }}.</p>
                        <p>By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of these terms, then you may not access the Service.</p>
                    </section>

                    <!-- Acceptance of Terms -->
                    <section class="legal-section">
                        <h2>2. Acceptance of Terms</h2>
                        <p>By creating an account or using {{ config('app.name') }}, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy.</p>
                        <ul>
                            <li>You must be at least 18 years old to use this Service</li>
                            <li>You must provide accurate and complete information</li>
                            <li>You are responsible for maintaining the security of your account</li>
                        </ul>
                    </section>

                    <!-- Description of Service -->
                    <section class="legal-section">
                        <h2>3. Description of Service</h2>
                        <p>{{ config('app.name') }} provides a comprehensive personal finance management platform that allows users to:</p>
                        <ul>
                            <li>Track income and expenses</li>
                            <li>Monitor investment portfolios</li>
                            <li>Set and track financial goals</li>
                            <li>Analyze spending patterns</li>
                            <li>Access financial insights and reports</li>
                        </ul>
                    </section>

                    <!-- User Accounts -->
                    <section class="legal-section">
                        <h2>4. User Accounts</h2>
                        <h3>4.1 Account Creation</h3>
                        <p>To access certain features, you must create an account. You agree to provide accurate, current, and complete information during registration.</p>
                        
                        <h3>4.2 Account Security</h3>
                        <p>You are responsible for:</p>
                        <ul>
                            <li>Maintaining the confidentiality of your account credentials</li>
                            <li>All activities that occur under your account</li>
                            <li>Immediately notifying us of any unauthorized access</li>
                        </ul>

                        <h3>4.3 Account Termination</h3>
                        <p>We reserve the right to terminate or suspend your account at our discretion, with or without notice, for violations of these Terms.</p>
                    </section>

                    <!-- Financial Data -->
                    <section class="legal-section">
                        <h2>5. Financial Data and Privacy</h2>
                        <h3>5.1 Data Collection</h3>
                        <p>We collect and process financial data you provide to deliver our services. This includes transaction data, account balances, and investment information.</p>
                        
                        <h3>5.2 Data Security</h3>
                        <p>We implement industry-standard security measures to protect your financial data, including:</p>
                        <ul>
                            <li>256-bit encryption for data transmission</li>
                            <li>Secure data storage with bank-level security</li>
                            <li>Regular security audits and updates</li>
                            <li>Limited access controls for our staff</li>
                        </ul>

                        <h3>5.3 Data Usage</h3>
                        <p>Your financial data is used exclusively to provide our services. We do not sell your personal financial information to third parties.</p>
                    </section>

                    <!-- Prohibited Uses -->
                    <section class="legal-section">
                        <h2>6. Prohibited Uses</h2>
                        <p>You may not use our Service:</p>
                        <ul>
                            <li>For any unlawful purpose or to solicit others to unlawful acts</li>
                            <li>To violate any international, federal, provincial, or state regulations or laws</li>
                            <li>To transmit, or procure the sending of, any advertising or promotional material</li>
                            <li>To impersonate or attempt to impersonate another user</li>
                            <li>To upload or transmit viruses or malicious code</li>
                            <li>To attempt to gain unauthorized access to our systems</li>
                        </ul>
                    </section>

                    <!-- Intellectual Property -->
                    <section class="legal-section">
                        <h2>7. Intellectual Property Rights</h2>
                        <p>The Service and its original content, features, and functionality are owned by {{ config('app.name') }} and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
                        <p>You may not reproduce, distribute, modify, or create derivative works of our content without explicit written permission.</p>
                    </section>

                    <!-- Disclaimers -->
                    <section class="legal-section">
                        <h2>8. Disclaimers</h2>
                        <h3>8.1 Financial Advice</h3>
                        <p class="disclaimer">{{ config('app.name') }} does not provide financial, investment, or legal advice. All information provided is for informational purposes only. You should consult with qualified professionals before making financial decisions.</p>
                        
                        <h3>8.2 Service Availability</h3>
                        <p>We strive to maintain 99.9% uptime but cannot guarantee uninterrupted service. The Service is provided "as is" without warranties of any kind.</p>

                        <h3>8.3 Data Accuracy</h3>
                        <p>While we make efforts to ensure data accuracy, you are responsible for verifying the accuracy of your financial information and reports.</p>
                    </section>

                    <!-- Limitation of Liability -->
                    <section class="legal-section">
                        <h2>9. Limitation of Liability</h2>
                        <p>In no event shall {{ config('app.name') }}, its directors, employees, or agents be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.</p>
                    </section>

                    <!-- Governing Law -->
                    <section class="legal-section">
                        <h2>10. Governing Law</h2>
                        <p>These Terms shall be governed by and construed in accordance with the laws of Thailand, without regard to its conflict of law provisions.</p>
                    </section>

                    <!-- Changes to Terms -->
                    <section class="legal-section">
                        <h2>11. Changes to Terms</h2>
                        <p>We reserve the right to modify these Terms at any time. We will notify users of significant changes via email or through our Service. Continued use after changes constitutes acceptance of the new Terms.</p>
                    </section>

                    <!-- Contact Information -->
                    <section class="legal-section">
                        <h2>12. Contact Information</h2>
                        <p>If you have any questions about these Terms of Service, please contact us:</p>
                        <div class="contact-info">
                            <p><strong>{{ config('app.name') }}</strong></p>
                            <p>Email: <a href="mailto:legal@monetro.io">legal@monetro.io</a></p>
                            <p>Address: 456 Digital Park, Mueang Chiang Mai, Chiang Mai 50000, Thailand</p>
                            <p>Phone: +66 53 123 456</p>
                        </div>
                    </section>
                </div>

                <!-- Footer Navigation -->
                <div class="legal-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <a href="{{ route('privacy') }}" class="btn btn-outline-primary">
                                <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                            </a>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="fas fa-home me-1"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>