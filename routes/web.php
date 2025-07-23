<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', function () {
    return view('hello');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    $user = auth()->user();
    $hasFinancialData = \App\Models\Transaction::where('user_id', $user->id)->exists();
    return view('dashboard', compact('hasFinancialData'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Legal Pages Routes
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

// AJAX Routes for Modal Content
Route::get('/api/legal/terms', [LegalController::class, 'getTermsModal'])->name('api.legal.terms');
Route::get('/api/legal/privacy', [LegalController::class, 'getPrivacyModal'])->name('api.legal.privacy');

// Record User Acceptance (for authenticated users)
Route::middleware('auth')->post('/api/legal/accept', [LegalController::class, 'recordAcceptance'])->name('api.legal.accept');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/finance.php';