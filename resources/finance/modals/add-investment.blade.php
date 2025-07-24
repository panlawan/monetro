{{-- resources/views/finance/modals/add-investment.blade.php --}}

<!-- Add Investment Modal -->
<div class="modal fade" id="addInvestmentModal" tabindex="-1" aria-labelledby="addInvestmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addInvestmentModalLabel">
                    <i class="fas fa-chart-pie me-2"></i>Add Investment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addInvestmentForm" method="POST" action="{{ route('finance.transactions.store') }}">
                @csrf
                <input type="hidden" name="type" value="investment">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="investment_amount" class="form-label">Total Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" class="form-control" id="investment_amount" name="amount" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="investment_unit_price" class="form-label">Unit Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" class="form-control" id="investment_unit_price" name="unit_price" 
                                           step="0.0001" min="0" placeholder="Price per unit">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="investment_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="investment_quantity" name="quantity" 
                                       step="0.0001" min="0" placeholder="Number of units">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="investment_symbol" class="form-label">Symbol/Code</label>
                                <input type="text" class="form-control" id="investment_symbol" name="symbol" 
                                       placeholder="e.g., AAPL, KBANK, BTC" style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="investment_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="investment_date" name="transaction_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="investment_description" class="form-label">Description *</label>
                        <input type="text" class="form-control" id="investment_description" name="description" 
                               placeholder="e.g., Buy Apple stock, Purchase Bitcoin" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="investment_category" class="form-label">Investment Type *</label>
                                <select class="form-select" id="investment_category" name="category_id" required>
                                    <option value="">Select type</option>
                                    @foreach(\App\Models\Category::where('user_id', auth()->id())->where('type', 'investment')->get() as $category)
                                        <option value="{{ $category->id }}">
                                            <i class="fas fa-{{ $category->icon }}"></i> {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="investment_payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="investment_payment_method" name="payment_method">
                                    <option value="">Select method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="broker_account">Broker Account</option>
                                    <option value="digital_wallet">Digital Wallet</option>
                                    <option value="cash">Cash</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="investment_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="investment_notes" name="notes" rows="3" 
                                  placeholder="Investment strategy, reasoning, etc..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-calculate amount from unit price and quantity
document.addEventListener('DOMContentLoaded', function() {
    const unitPriceInput = document.getElementById('investment_unit_price');
    const quantityInput = document.getElementById('investment_quantity');
    const amountInput = document.getElementById('investment_amount');
    
    function calculateAmount() {
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const total = unitPrice * quantity;
        
        if (total > 0) {
            amountInput.value = total.toFixed(2);
        }
    }
    
    unitPriceInput.addEventListener('input', calculateAmount);
    quantityInput.addEventListener('input', calculateAmount);
    
    // Auto-calculate quantity from amount and unit price
    amountInput.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        
        if (amount > 0 && unitPrice > 0) {
            quantityInput.value = (amount / unitPrice).toFixed(4);
        }
    });
});
</script>