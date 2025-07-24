{{-- resources/views/finance/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-chart-line text-primary me-2"></i>Finance Dashboard
            </h2>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                <i class="fas fa-plus me-1"></i>Add Transaction
            </button>
        </div>
    </x-slot>

    <!-- Add Font Awesome and Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Finance Navigation -->
    @include('layouts.finance-nav')

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Monthly Income -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-success border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Monthly Income</div>
                            <div class="h5 mb-0 fw-bold text-dark">
                                ฿{{ number_format($monthlyIncome, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-up fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Expense -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-danger border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Monthly Expense</div>
                            <div class="h5 mb-0 fw-bold text-dark">
                                ฿{{ number_format($monthlyExpense, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-down fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Balance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-{{ $monthlyBalance >= 0 ? 'primary' : 'warning' }} border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-{{ $monthlyBalance >= 0 ? 'primary' : 'warning' }} text-uppercase mb-1">
                                Monthly Balance</div>
                            <div class="h5 mb-0 fw-bold text-dark">
                                ฿{{ number_format($monthlyBalance, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-info border-4 shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Savings Rate</div>
                            <div class="h5 mb-0 fw-bold text-dark">
                                {{ number_format($savingsRate, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Monthly Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Expense Breakdown</h6>
                </div>
                <div class="card-body">
                    @if($expenseByCategory->count() > 0)
                        <div class="chart-pie pt-4 pb-2" style="height: 200px;">
                            <canvas id="expenseChart"></canvas>
                        </div>
                        <div class="mt-4">
                            @foreach($expenseByCategory->take(5) as $category)
                                <div class="d-flex align-items-center mb-2">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle d-inline-block" 
                                             style="width: 12px; height: 12px; background-color: {{ $category['color'] }};"></div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">{{ $category['name'] }}</small>
                                        <div class="fw-bold">฿{{ number_format($category['amount'], 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No expense data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Recent Transactions</h6>
                    <a href="{{ route('finance.transactions') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle p-2 text-white" 
                                                             style="background-color: {{ $transaction->category->color ?? '#6c757d' }};">
                                                            <i class="fas {{ $transaction->category->icon ?? 'fa-circle' }}"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div class="fw-bold">{{ $transaction->description }}</div>
                                                        <small class="text-muted">{{ $transaction->category->name }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                                            {{ $transaction->type === 'income' ? '+' : '-' }}฿{{ number_format($transaction->amount, 2) }}
                                                        </div>
                                                        <small class="text-muted">{{ $transaction->transaction_date->format('M d') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No transactions yet</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                Add First Transaction
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Goals Progress -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Goals Progress</h6>
                    <a href="{{ route('finance.goals') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($activeGoals->count() > 0)
                        @foreach($activeGoals as $goal)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold">{{ $goal->name }}</span>
                                    <span class="text-muted">{{ number_format($goal->progress_percentage, 1) }}%</span>
                                </div>
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar" 
                                         style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }};"
                                         role="progressbar"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">฿{{ $goal->formatted_current_amount }}</small>
                                    <small class="text-muted">฿{{ $goal->formatted_target_amount }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-target fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active goals</p>
                            <a href="{{ route('finance.goals') }}" class="btn btn-primary btn-sm">
                                Create Goal
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTransactionForm" action="{{ route('finance.transactions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required id="transactionType">
                                    <option value="">Select Type</option>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required id="categorySelect">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category data for form
        const incomeCategories = @json($incomeCategories);
        const expenseCategories = @json($expenseCategories);
        
        // Update categories when type changes
        document.getElementById('transactionType').addEventListener('change', function() {
            const categorySelect = document.getElementById('categorySelect');
            const type = this.value;
            
            // Clear existing options
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            
            // Add relevant categories
            const categories = type === 'income' ? incomeCategories : expenseCategories;
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        });

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
        if (monthlyTrendCtx) {
            const monthlyData = @json($monthlyTrend);
            
            new Chart(monthlyTrendCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [{
                        label: 'Income',
                        data: monthlyData.map(item => item.income),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        fill: true
                    }, {
                        label: 'Expense',
                        data: monthlyData.map(item => item.expense),
                        borderColor: '#e74a3b',
                        backgroundColor: 'rgba(231, 74, 59, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '฿' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ฿' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart');
        if (expenseCtx) {
            const expenseData = @json($expenseByCategory);
            
            if (expenseData.length > 0) {
                new Chart(expenseCtx, {
                    type: 'doughnut',
                    data: {
                        labels: expenseData.map(item => item.name),
                        datasets: [{
                            data: expenseData.map(item => item.amount),
                            backgroundColor: expenseData.map(item => item.color)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ฿' + context.parsed.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Handle Add Transaction Form
        document.getElementById('addTransactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error adding transaction: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding transaction');
            });
        });
    });
    </script>
</x-app-layout>