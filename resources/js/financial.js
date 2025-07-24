// resources/js/financial.js
// Financial Management JavaScript Functions

class FinancialManager {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeCharts();
    }

    bindEvents() {
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#addIncomeForm, #addExpenseForm, #addInvestmentForm')) {
                e.preventDefault();
                this.handleTransactionSubmit(e.target);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key.toLowerCase()) {
                    case 'i':
                        e.preventDefault();
                        this.openModal('addIncomeModal');
                        break;
                    case 'e':
                        e.preventDefault();
                        this.openModal('addExpenseModal');
                        break;
                    case 'n':
                        e.preventDefault();
                        this.openModal('addInvestmentModal');
                        break;
                }
            }
        });

        // Auto-save draft transactions
        this.initAutoSave();
    }

    async handleTransactionSubmit(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                this.showNotification('success', data.message);
                
                // Close modal
                const modal = form.closest('.modal');
                bootstrap.Modal.getInstance(modal)?.hide();
                
                // Reset form
                form.reset();
                form.querySelector('input[name="transaction_date"]').value = new Date().toISOString().split('T')[0];
                
                // Refresh dashboard data
                this.refreshDashboard();
                
            } else {
                this.showNotification('error', data.message || 'An error occurred');
            }

        } catch (error) {
            console.error('Transaction error:', error);
            this.showNotification('error', 'Network error. Please try again.');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            new bootstrap.Modal(modal).show();
            // Focus first input
            setTimeout(() => {
                const firstInput = modal.querySelector('input:not([type="hidden"]), select');
                firstInput?.focus();
            }, 300);
        }
    }

    showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    async refreshDashboard() {
        try {
            // Refresh specific dashboard components
            const balanceCards = document.querySelectorAll('[x-data="financialDashboard()"]');
            balanceCards.forEach(card => {
                // Trigger Alpine.js refresh if available
                if (window.Alpine && card._x_dataStack) {
                    card._x_dataStack[0].init?.();
                }
            });

            // Refresh recent transactions
            this.refreshRecentTransactions();

        } catch (error) {
            console.error('Dashboard refresh error:', error);
        }
    }

    async refreshRecentTransactions() {
        // This would fetch and update the recent transactions list
        // Implementation depends on your specific needs
    }

    initAutoSave() {
        // Auto-save form data to localStorage
        const forms = document.querySelectorAll('#addIncomeForm, #addExpenseForm, #addInvestmentForm');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Load saved data
                const savedValue = localStorage.getItem(`draft_${form.id}_${input.name}`);
                if (savedValue && input.type !== 'date') {
                    input.value = savedValue;
                }

                // Save on input
                input.addEventListener('input', () => {
                    if (input.value) {
                        localStorage.setItem(`draft_${form.id}_${input.name}`, input.value);
                    } else {
                        localStorage.removeItem(`draft_${form.id}_${input.name}`);
                    }
                });
            });

            // Clear draft on successful submission
            form.addEventListener('submit', () => {
                setTimeout(() => {
                    inputs.forEach(input => {
                        localStorage.removeItem(`draft_${form.id}_${input.name}`);
                    });
                }, 1000);
            });
        });
    }

    initializeCharts() {
        // Initialize Chart.js charts
        if (typeof Chart !== 'undefined') {
            this.initFinancialChart();
            this.initCategoryChart();
        }
    }

    initFinancialChart() {
        const canvas = document.getElementById('financialChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // This would use actual data passed from the controller
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Income',
                    data: [65000, 59000, 80000, 81000, 56000, 85000],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Expenses',
                    data: [28000, 48000, 40000, 19000, 86000, 27000],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + new Intl.NumberFormat('th-TH').format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    initCategoryChart() {
        const canvas = document.getElementById('categoryChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Food', 'Transport', 'Shopping', 'Bills', 'Entertainment'],
                datasets: [{
                    data: [30, 20, 25, 15, 10],
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}

// Global functions for template usage
window.createDefaultCategories = async function(type) {
    try {
        const response = await fetch('/finance/categories/defaults', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type })
        });

        const data = await response.json();
        
        if (data.success) {
            // Reload the page to show new categories
            location.reload();
        } else {
            alert('Error creating categories: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Network error. Please try again.');
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.financialManager = new FinancialManager();
});