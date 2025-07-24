<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-chart-pie text-primary me-2"></i>Budgets
            </h2>
        </div>
    </x-slot>

    <!-- Finance Navigation -->
    @include('layouts.finance-nav')

    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-pie fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Budget Management</h5>
            <p class="text-muted">Budget feature is coming soon! You can track your spending through transactions for now.</p>
            <a href="{{ route('finance.transactions') }}" class="btn btn-primary">
                <i class="fas fa-receipt me-1"></i>View Transactions
            </a>
        </div>
    </div>
</x-app-layout>