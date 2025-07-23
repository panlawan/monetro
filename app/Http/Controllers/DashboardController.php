<?php
// app/Http/Controllers/DashboardController.php (อัพเดท main dashboard)

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        
        // Check if user has financial data
        $hasFinancialData = \App\Models\Transaction::where('user_id', $user->id)->exists();
        
        return view('dashboard', compact('hasFinancialData'));
    }
}