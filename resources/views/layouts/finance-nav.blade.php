{{-- resources/views/layouts/finance-nav.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-4">
    <div class="container">
        <div class="navbar-nav">
            <a class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active fw-bold' : '' }}" 
               href="{{ route('finance.dashboard') }}">
                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('finance.transactions*') ? 'active fw-bold' : '' }}" 
               href="{{ route('finance.transactions') }}">
                <i class="fas fa-receipt me-1"></i>Transactions
            </a>
            <a class="nav-link {{ request()->routeIs('finance.goals*') ? 'active fw-bold' : '' }}" 
               href="{{ route('finance.goals') }}">
                <i class="fas fa-target me-1"></i>Goals
            </a>
            <a class="nav-link {{ request()->routeIs('finance.budgets*') ? 'active fw-bold' : '' }}" 
               href="{{ route('finance.budgets') }}">
                <i class="fas fa-chart-pie me-1"></i>Budgets
            </a>
            <a class="nav-link {{ request()->routeIs('finance.reports*') ? 'active fw-bold' : '' }}" 
               href="{{ route('finance.reports') }}">
                <i class="fas fa-chart-bar me-1"></i>Reports
            </a>
        </div>
    </div>
</nav>