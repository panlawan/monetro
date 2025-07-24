{{-- resources/views/finance/modals/add-income.blade.php --}}

<!-- Add Income Modal -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addIncomeModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Income
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addIncomeForm" method="POST" action="{{ route('finance.transactions.store') }}">
                @csrf
                <input type="hidden" name="type" value="income">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_amount" class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">฿</span>
                                    <input type="number" class="form-control" id="income_amount" name="amount" 
                                           step="0.01" min="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="income_date" name="transaction_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="income_description" class="form-label">Description *</label>
                        <input type="text" class="form-control" id="income_description" name="description" 
                               placeholder="e.g., Salary, Freelance payment, Gift" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_category" class="form-label">Category *</label>
                                <select class="form-select" id="income_category" name="category_id" required>
                                    <option value="">Select category</option>
                                    @foreach(\App\Models\Category::where('user_id', auth()->id())->where('type', 'income')->get() as $category)
                                        <option value="{{ $category->id }}">
                                            <i class="fas fa-{{ $category->icon }}"></i> {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <button type="button" class="btn btn-link btn-sm p-0" onclick="createDefaultCategories('income')">
                                        Create default income categories
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="income_payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="income_payment_method" name="payment_method">
                                    <option value="">Select method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="digital_wallet">Digital Wallet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="income_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="income_notes" name="notes" rows="3" 
                                  placeholder="Additional details..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Add Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>