{{-- resources/views/finance/modals/add-expense.blade.php --}}

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="addExpenseModalLabel">
                    <i class="fas fa-minus-circle me-2"></i>Add Expense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addExpenseForm" method="POST" action="{{ route('finance.transactions.store') }}">
                @csrf
                <input type="hidden" name="type" value="expense">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_amount" class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" class="form-control" id="expense_amount" name="amount" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="expense_date" name="transaction_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_description" class="form-label">Description *</label>
                        <input type="text" class="form-control" id="expense_description" name="description" 
                               placeholder="e.g., Grocery shopping, Gas, Restaurant" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_category" class="form-label">Category *</label>
                                <select class="form-select" id="expense_category" name="category_id" required>
                                    <option value="">Select category</option>
                                    @foreach(\App\Models\Category::where('user_id', auth()->id())->where('type', 'expense')->get() as $category)
                                        <option value="{{ $category->id }}">
                                            <i class="fas fa-{{ $category->icon }}"></i> {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <button type="button" class="btn btn-link btn-sm p-0" onclick="createDefaultCategories('expense')">
                                        Create default expense categories
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="expense_payment_method" name="payment_method">
                                    <option value="">Select method</option>
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="digital_wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="expense_location" name="location" 
                                       placeholder="Where was this expense made?">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_reference" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="expense_reference" name="reference_number" 
                                       placeholder="Receipt #, Transaction ID">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="expense_notes" name="notes" rows="3" 
                                  placeholder="Additional details..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i>Add Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>