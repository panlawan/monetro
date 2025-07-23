{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Monetro.io') }} - Personal Finance Management</title>

    <!-- Meta Description -->
    <meta name="description"
        content="Take control of your financial life with Monetro.io. Track income, expenses, and investments across stocks, crypto, forex, and more in real-time.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap & SCSS via Vite -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="welcome-layout">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-chart-line me-2 hero-icon"></i>{{ config('app.name', 'Monetro.io') }}
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTMgN0gyN00zIDE1SDI3TTMgMjNIMjciIHN0cm9rZT0iI0ZGRiIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz4KPC9zdmc+');"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2"></i>Profile</a></li>
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

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-content text-center py-5 loading-animation">
                <div class="mb-5">
                    <i class="fas fa-coins fa-4x mb-4 hero-icon"></i>
                </div>
                <h1 class="display-4 fw-bold mb-4">
                    Take Control of Your Financial Life
                </h1>
                <p class="lead mb-5 mx-auto" style="max-width: 600px; font-size: 1.25rem;">
                    Track income, expenses, and investments across stocks, crypto, forex, and more - all in real-time
                    with advanced analytics and insights.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-primary">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="btn-hero btn-hero-outline">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Market Ticker -->
    <div class="market-ticker">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <span class="fw-bold me-4 text-warning">
                    <i class="fas fa-chart-area me-2"></i>LIVE MARKET:
                </span>
                <div class="ticker-content">
                    <span class="me-5">
                        <i class="fab fa-bitcoin text-warning"></i> BTC: ฿1,125,840
                        <span class="text-success">↑2.4%</span>
                    </span>
                    <span class="me-5">
                        <i class="fab fa-ethereum text-info"></i> ETH: ฿82,450
                        <span class="text-success">↑1.8%</span>
                    </span>
                    <span class="me-5">
                        <i class="fas fa-chart-line text-primary"></i> SET: 1,458
                        <span class="text-danger">↓0.5%</span>
                    </span>
                    <span class="me-5">
                        <i class="fas fa-dollar-sign text-success"></i> USD/THB: 35.42
                        <span class="text-success">↑0.2%</span>
                    </span>
                    <span class="me-5">
                        <i class="fas fa-coins text-warning"></i> GOLD: ฿75,950
                        <span class="text-success">↑0.8%</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section py-5">
        <div class="container py-5">
            <div class="text-center mb-5 loading-animation" style="animation-delay: 0.2s;">
                <h2 class="text-uppercase fw-semibold small mb-2" style="color: var(--monetro-brand-primary);">
                    <i class="fas fa-star me-2"></i>Features
                </h2>
                <h3 class="display-5 fw-bold mb-4">
                    Everything you need to manage your money
                </h3>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    Powerful tools and insights to help you make smarter financial decisions
                </p>
            </div>

            <div class="row g-4">
                <!-- Expense Tracking -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.3s;">
                        <div class="feature-icon">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Expense Tracking</h4>
                        <p class="text-muted">
                            Automatically categorize transactions and see where your money goes with beautiful
                            visualizations and detailed reports.
                        </p>
                    </div>
                </div>

                <!-- Investment Portfolio -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.4s;">
                        <div class="feature-icon">
                            <i class="fas fa-chart-pie fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Investment Portfolio</h4>
                        <p class="text-muted">
                            Track all your investments in one place - stocks, crypto, forex, and more with real-time
                            price updates and performance analytics.
                        </p>
                    </div>
                </div>

                <!-- Smart Alerts -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.5s;">
                        <div class="feature-icon">
                            <i class="fas fa-bell fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Smart Alerts</h4>
                        <p class="text-muted">
                            Get notified about important financial events, price movements, and unusual spending
                            patterns to stay on top of your finances.
                        </p>
                    </div>
                </div>

                <!-- Multi-Currency -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.6s;">
                        <div class="feature-icon">
                            <i class="fas fa-exchange-alt fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Multi-Currency</h4>
                        <p class="text-muted">
                            Support for all major currencies with automatic conversion rates. Perfect for travelers
                            and international investors.
                        </p>
                    </div>
                </div>

                <!-- Bank-Level Security -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.7s;">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Bank-Level Security</h4>
                        <p class="text-muted">
                            256-bit encryption and read-only access to protect your financial data. Your security
                            and privacy are our top priorities.
                        </p>
                    </div>
                </div>

                <!-- Mobile Friendly -->
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card loading-animation" style="animation-delay: 0.8s;">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt fa-lg"></i>
                        </div>
                        <h4 class="mb-3">Mobile Friendly</h4>
                        <p class="text-muted">
                            Full-featured mobile experience. Track your finances on the go with our responsive web
                            application.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section py-5">
        <div class="container py-5">
            <div class="text-center loading-animation" style="animation-delay: 0.9s;">
                <h2 class="display-5 fw-bold mb-4" style="color: var(--monetro-brand-primary);">
                    Ready to take control of your finances?
                </h2>
                <p class="lead mb-5 text-muted mx-auto" style="max-width: 600px;">
                    Join thousands of users who have already transformed their financial lives with Monetro.io
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('register') }}" class="btn-hero btn-hero-gradient">
                        <i class="fas fa-rocket me-2"></i>Start Your Journey
                    </a>
                    <a href="#features" class="btn-hero btn-hero-outline" style="border-color: var(--monetro-brand-primary); color: var(--monetro-brand-primary);">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-custom py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-line fa-2x me-3" style="color: var(--monetro-brand-primary);"></i>
                        <div>
                            <h5 class="mb-0">{{ config('app.name', 'Monetro.io') }}</h5>
                            <small class="text-light">Personal Finance Management</small>
                        </div>
                    </div>
                    <p class="text-light">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex flex-column align-items-md-end">
                        <div class="mb-3">
                            <a href="#" class="text-light me-3 fs-5"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-light me-3 fs-5"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-light me-3 fs-5"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-light fs-5"><i class="fab fa-github"></i></a>
                        </div>
                        <small class="text-light d-block">
                            Monetro Co., Ltd. · 456 Digital Park, Mueang Chiang Mai,<br>
                            Chiang Mai 50000 · Tel: +66 53 123 456
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Test CSS Button -->
    <a href="/test-css" class="test-button no-print">
        <i class="fas fa-flask me-1"></i>Test CSS
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('🎉 Monetro.io Welcome Page Loaded');
            console.log('✅ Bootstrap:', typeof bootstrap !== 'undefined' ? 'Loaded' : 'Not Loaded');
            console.log('✅ Font Awesome:', document.querySelector('.fas') ? 'Loaded' : 'Not Loaded');

            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = '0s';
                        entry.target.classList.add('loading-animation');
                    }
                });
            }, observerOptions);

            // Observe all elements that should animate
            document.querySelectorAll('.feature-card').forEach(card => {
                observer.observe(card);
            });

            // Add floating effect to hero icons on hover
            document.querySelectorAll('.hero-icon').forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.animationDuration = '0.5s';
                });
                
                icon.addEventListener('mouseleave', function() {
                    this.style.animationDuration = '3s';
                });
            });

            // Parallax effect for hero section
            let ticking = false;
            
            function updateParallax() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelector('.hero-section');
                const speed = scrolled * 0.5;
                
                if (parallax) {
                    parallax.style.transform = `translateY(${speed}px)`;
                }
                ticking = false;
            }

            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }

            window.addEventListener('scroll', requestTick);

            // Navbar background on scroll
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar-custom');
                if (window.scrollY > 50) {
                    navbar.style.background = 'rgba(255, 255, 255, 0.15)';
                    navbar.style.backdropFilter = 'blur(15px)';
                } else {
                    navbar.style.background = 'rgba(255, 255, 255, 0.1)';
                    navbar.style.backdropFilter = 'blur(10px)';
                }
            });

            // Market ticker animation
            const ticker = document.querySelector('.ticker-content');
            if (ticker) {
                // Clone ticker content for seamless loop
                const clone = ticker.cloneNode(true);
                ticker.parentNode.appendChild(clone);
            }

            // Performance optimization - use passive listeners
            document.addEventListener('touchstart', function() {}, { passive: true });
            document.addEventListener('touchmove', function() {}, { passive: true });

            // Preload critical images (if any)
            const preloadImages = [];
            preloadImages.forEach(src => {
                const img = new Image();
                img.src = src;
            });

            console.log('🎨 Welcome page fully initialized');
        });

        // Service Worker registration (future enhancement)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Analytics tracking (placeholder)
        function trackEvent(eventName, properties) {
            console.log('📊 Event:', eventName, properties);
            // Implement your analytics tracking here
        }

        // Track button clicks
        document.addEventListener('click', function(e) {
            if (e.target.matches('.btn-hero')) {
                trackEvent('hero_button_click', {
                    button_text: e.target.textContent.trim(),
                    button_type: e.target.className
                });
            }
        });
    </script>
</body>

</html>