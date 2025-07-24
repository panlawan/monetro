<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                <i class="fas fa-chart-bar text-primary me-2"></i>Financial Reports
            </h2>
        </div>
    </x-slot>

    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Finance Navigation -->
    @include('layouts.finance-nav')

    <!-- Monthly Trend -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="fw-bold text-primary mb-0">12-Month Financial Trend</h6>
        </div>
        <div class="card-body">
            <div style="height: 400px;">
                <canvas id="monthlyReportChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Expense Categories -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="fw-bold text-primary mb-0">Top Expense Categories (Last 3 Months)</h6>
                </div>
                <div class="card-body">
                    @if($topExpenseCategories->count() > 0)
                        @foreach($topExpenseCategories as $index => $category)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle p-2 text-white" 
                                         style="background-color: {{ $category->category->color ?? '#6c757d' }};">
                                        <i class="fas {{ $category->category->icon ?? 'fa-circle' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">{{ $category->category->name }}</div>
                                    <div class="text-danger fw-bold">฿{{ number_format($category->total, 2) }}</div>
                                </div>
                                <div class="text-muted">
                                    #{{ $index + 1 }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No expense data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Income Sources -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="fw-bold text-primary mb-0">Income Sources (Last 3 Months)</h6>
                </div>
                <div class="card-body">
                    @if($incomeSources->count() > 0)
                        @foreach($incomeSources as $index => $source)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle p-2 text-white" 
                                         style="background-color: {{ $source->category->color ?? '#1cc88a' }};">
                                        <i class="fas {{ $source->category->icon ?? 'fa-circle' }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">{{ $source->category->name }}</div>
                                    <div class="text-success fw-bold">฿{{ number_format($source->total, 2) }}</div>
                                </div>
                                <div class="text-muted">
                                    #{{ $index + 1 }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No income data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Report Chart
        const ctx = document.getElementById('monthlyReportChart');
        if (ctx) {
            const monthlyData = @json($monthlyData);
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [{
                        label: 'Income',
                        data: monthlyData.map(item => item.income),
                        backgroundColor: 'rgba(28, 200, 138, 0.8)',
                        borderColor: '#1cc88a',
                        borderWidth: 1
                    }, {
                        label: 'Expense',
                        data: monthlyData.map(item => item.expense),
                        backgroundColor: 'rgba(231, 74, 59, 0.8)',
                        borderColor: '#e74a3b',
                        borderWidth: 1
                    }, {
                        label: 'Balance',
                        data: monthlyData.map(item => item.balance),
                        type: 'line',
                        borderColor: '#36b9cc',
                        backgroundColor: 'rgba(54, 185, 204, 0.1)',
                        fill: false,
                        tension: 0.4
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
    });
    </script>
</x-app-layout>