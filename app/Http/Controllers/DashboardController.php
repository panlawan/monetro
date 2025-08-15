<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(private ReportService $report)
    {
    }

    public function index(Request $request)
    {
        // อ่าน theme preference เผื่อจะใช้กำหนด class ใน Blade
        $pref = optional($request->user()->preference)->theme_preference ?? 'auto';
        return view('dashboard', compact('pref'));
    }


    
    // app/Http/Controllers/DashboardController.php
    public function monthly(Request $request, ReportService $report)
    {
        $userId = (int) auth()->id();
        $months = (int) $request->query('months', 12);
        $months = max(1, min(36, $months)); // guard

        try {
            $series = $report->monthlySeries($userId, $months);
            return response()->json($series);
        } catch (\Throwable $e) {
            Log::error('Monthly series failed', [
                'userId' => $userId,
                'months' => $months,
                'err' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }



    public function summary(Request $request)
    {
        $data = $this->report->topLevelSummary(
            $request->user()->id,
            $request->query('from'),
            $request->query('to')
        );
        return response()->json($data);
    }

    public function monthlyChart(Request $request)
    {
        $months = (int) $request->query('months', 12);
        return response()->json(
            $this->report->monthlySeries($request->user()->id, $months)
        );
    }


    public function setTheme(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark,auto']);
        // บันทึกลง user_preferences.theme_preference (คอลัมน์มีแล้ว) 
        $pref = $request->user()->preference;
        if ($pref) {
            $pref->update(['theme_preference' => $request->theme]);
        } else {
            $request->user()->preference()->create(['theme_preference' => $request->theme]);
        }
        return back();
    }
}
