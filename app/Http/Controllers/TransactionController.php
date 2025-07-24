<?php
// app/Http/Controllers/TransactionController.php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $query = Transaction::forUser($userId)
            ->with('category')
            ->latest('transaction_date');
        
        // Filters
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        
        $transactions = $query->paginate(20);
        $categories = Category::forUser($userId)->active()->get();
        
        return view('finance.transactions.index', compact('transactions', 'categories'));
    }
    
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:income,expense,investment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);
        
        // Convert expense to negative amount
        $amount = $request->amount;
        if ($request->type === 'expense') {
            $amount = -abs($amount);
        }
        
        $transaction = Transaction::create([
            'type' => $request->type,
            'amount' => $amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'location' => $request->location,
            'status' => 'completed',
            'processed_at' => now(),
        ]);
        
        // Update related budgets
        if ($request->type === 'expense') {
            $this->updateBudgets($request->category_id, $transaction->transaction_date);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Transaction added successfully',
            'transaction' => $transaction->load('category')
        ]);
    }
    
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        // Check ownership
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);
        
        // Convert expense to negative amount
        $amount = $request->amount;
        if ($transaction->type === 'expense') {
            $amount = -abs($amount);
        }
        
        $transaction->update([
            'amount' => $amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'category_id' => $request->category_id,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'location' => $request->location,
        ]);
        
        // Update related budgets
        if ($transaction->type === 'expense') {
            $this->updateBudgets($request->category_id, $transaction->transaction_date);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction->load('category')
        ]);
    }
    
    public function destroy(Transaction $transaction): JsonResponse
    {
        // Check ownership
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $categoryId = $transaction->category_id;
        $transactionDate = $transaction->transaction_date;
        $isExpense = $transaction->type === 'expense';
        
        $transaction->delete();
        
        // Update related budgets
        if ($isExpense) {
            $this->updateBudgets($categoryId, $transactionDate);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    }
    
    private function updateBudgets($categoryId, $transactionDate)
    {
        $budgets = Budget::where('user_id', auth()->id())
            ->where('category_id', $categoryId)
            ->where('start_date', '<=', $transactionDate)
            ->where('end_date', '>=', $transactionDate)
            ->get();
            
        foreach ($budgets as $budget) {
            $budget->updateSpent();
        }
    }
    
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
        
        $file = $request->file('file');
        $csvData = array_map('str_getcsv', file($file->path()));
        $headers = array_shift($csvData); // Remove header row
        
        $imported = 0;
        $errors = [];
        
        foreach ($csvData as $index => $row) {
            try {
                if (count($row) < 4) continue; // Skip incomplete rows
                
                $data = array_combine($headers, $row);
                
                // Find or create category
                $category = Category::firstOrCreate([
                    'name' => $data['category'] ?? 'Uncategorized',
                    'user_id' => auth()->id(),
                    'type' => $data['type'] ?? 'expense'
                ]);
                
                $amount = (float) $data['amount'];
                if (($data['type'] ?? 'expense') === 'expense') {
                    $amount = -abs($amount);
                }
                
                Transaction::create([
                    'type' => $data['type'] ?? 'expense',
                    'amount' => $amount,
                    'description' => $data['description'] ?? 'Imported transaction',
                    'transaction_date' => $data['date'] ?? now()->toDateString(),
                    'category_id' => $category->id,
                    'user_id' => auth()->id(),
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);
                
                $imported++;
                
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$imported} transactions imported successfully",
            'imported' => $imported,
            'errors' => $errors
        ]);
    }
}