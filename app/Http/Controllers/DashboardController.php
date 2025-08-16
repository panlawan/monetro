<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display main dashboard
     */
    public function index(Request $request)
    {
        try {
            // อ่าน theme preference
            $pref = $request->user()->preference->theme_preference ?? 'auto';
            
            return view('dashboard', compact('pref'));
        } catch (\Throwable $e) {
            Log::error('Dashboard index failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            
            // Fallback ถ้าไม่มี preference table
            $pref = 'auto';
            return view('dashboard', compact('pref'));
        }
    }

    /**
     * Get dashboard summary data (API endpoint)
     */
    public function summary(Request $request)
    {
        try {
            $data = $this->reportService->topLevelSummary(
                $request->user()->id,
                $request->query('from'),
                $request->query('to')
            );
            
            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error('Dashboard summary failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'total_income' => 0,
                'total_expense' => 0,
                'net_income' => 0,
                'transaction_count' => 0,
            ]);
        }
    }

    /**
     * Get monthly chart data (API endpoint)
     */
    public function monthly(Request $request)
    {
        try {
            $userId = (int) auth()->id();
            $months = (int) $request->query('months', 12);
            $months = max(1, min(36, $months)); // จำกัดให้อยู่ระหว่าง 1-36 เดือน

            $series = $this->reportService->monthlySeries($userId, $months);
            return response()->json($series);
        } catch (\Throwable $e) {
            Log::error('Monthly chart failed', [
                'userId' => auth()->id(),
                'months' => $request->query('months', 12),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'months' => [],
                'income' => [],
                'expense' => [],
                'net' => [],
            ]);
        }
    }

    /**
     * Set user theme preference
     */
    public function setTheme(Request $request)
    {
        try {
            $request->validate(['theme' => 'required|in:light,dark,auto']);
            
            $user = $request->user();
            
            // สร้างหรืออัปเดต preference
            if ($user->preference) {
                $user->preference->update(['theme_preference' => $request->theme]);
            } else {
                $user->preference()->create(['theme_preference' => $request->theme]);
            }
            
            return back()->with('success', 'Theme updated successfully');
        } catch (\Throwable $e) {
            Log::error('Theme update failed', [
                'userId' => auth()->id(),
                'theme' => $request->theme,
                'error' => $e->getMessage(),
            ]);
            
            return back()->withErrors(['theme' => 'Failed to update theme']);
        }
    }
}