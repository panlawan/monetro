<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionStoreRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Show the form for creating a new transaction.
     */
    public function create(): View
    {
        $categoryClass = 'App\\Models\\Category';
        $categories = class_exists($categoryClass) ? $categoryClass::all() : [];

        return view('transactions.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(TransactionStoreRequest $request): RedirectResponse
    {
        Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $request->input('category_id'),
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'transaction_date' => $request->input('transaction_date'),
            'note' => $request->input('note'),
        ]);

        return redirect()->route('dashboard');
    }
}
