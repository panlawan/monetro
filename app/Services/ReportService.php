<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportService
{
    /**
     * สรุปตัวเลขบนการ์ดด้านบนของ Dashboard
     */
    public function topLevelSummary(int $userId, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate] = $this->normalizeRange($from, $to);

        try {
            // ไม่ส่งช่วงวันที่ → ลองดึงจากตารางสรุปรายเดือนก่อน
            if (!$fromDate || !$toDate) {
                if (Schema::hasTable('monthly_summaries')) {
                    $row = DB::table('monthly_summaries')
                        ->where('user_id', $userId)
                        ->orderByDesc('year')->orderByDesc('month')
                        ->first();

                    if ($row) {
                        return [
                            'total_income'      => (float) $row->total_income,
                            'total_expense'     => (float) $row->total_expense,
                            'net_income'        => (float) $row->net_income,
                            'total_transfers'   => (float) ($row->total_transfers ?? 0),
                            'transaction_count' => (int)   ($row->transaction_count ?? 0),
                            'transfer_count'    => (int)   ($row->transfer_count ?? 0),
                        ];
                    }
                }

                // fallback: ใช้เดือนปัจจุบัน
                $fromDate = now()->startOfMonth();
                $toDate   = now()->endOfMonth();
            }

            if (!Schema::hasTable('transactions')) {
                return [
                    'total_income' => 0, 'total_expense' => 0, 'net_income' => 0,
                    'total_transfers' => 0, 'transaction_count' => 0, 'transfer_count' => 0,
                ];
            }

            $tx = DB::table('transactions')
                ->selectRaw("
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as total_expense,
                    COUNT(*) as transaction_count
                ")
                ->where('user_id', $userId)
                ->whereBetween('transaction_date', [
                    $fromDate->toDateString(),
                    $toDate->toDateString(),
                ])
                ->first();

            $income  = (float)($tx->total_income ?? 0);
            $expense = (float)($tx->total_expense ?? 0);

            $totalTransfers = 0; $transferCount = 0;
            if (Schema::hasTable('transfers')) {
                $tr = DB::table('transfers')
                    ->selectRaw("SUM(amount) as total, COUNT(*) as cnt")
                    ->where('user_id', $userId)
                    ->whereBetween('transfer_date', [
                        $fromDate->toDateString(),
                        $toDate->toDateString(),
                    ])
                    ->first();
                $totalTransfers = (float)($tr->total ?? 0);
                $transferCount  = (int)  ($tr->cnt ?? 0);
            }

            return [
                'total_income'      => $income,
                'total_expense'     => $expense,
                'net_income'        => $income - $expense,
                'total_transfers'   => $totalTransfers,
                'transaction_count' => (int)($tx->transaction_count ?? 0),
                'transfer_count'    => $transferCount,
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'total_income' => 0, 'total_expense' => 0, 'net_income' => 0,
                'total_transfers' => 0, 'transaction_count' => 0, 'transfer_count' => 0,
            ];
        }
    }

    /**
     * ซีรีส์รายเดือนสำหรับกราฟ (income/expense/net)
     */
    // public function monthlySeries(int $userId, int $months = 12): array
    // {
    //     if (!Schema::hasTable('transactions')) {
    //         return ['labels' => [], 'income' => [], 'expense' => [], 'net' => []];
    //     }

    //     $end   = Carbon::now()->startOfMonth();
    //     $start = $end->copy()->subMonths($months - 1);
    //     $endOfRange = $end->copy()->endOfMonth();

    //     $rows = Transaction::query()
    //         ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as ym")
    //         ->selectRaw("SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END) as income")
    //         ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
    //         ->where('user_id', $userId)
    //         ->whereBetween('transaction_date', [$start->toDateString(), $endOfRange->toDateString()])
    //         ->groupBy('ym')
    //         ->orderBy('ym')
    //         ->get()
    //         ->keyBy('ym');

    //     $labels = $income = $expense = $net = [];
    //     $cursor = $start->copy();

    //     while ($cursor->lte($end)) {
    //         $ym = $cursor->format('Y-m');
    //         $labels[] = $cursor->format('M Y');

    //         $inc = (float)($rows[$ym]->income  ?? 0);
    //         $exp = (float)($rows[$ym]->expense ?? 0);

    //         $income[]  = $inc;
    //         $expense[] = $exp;
    //         $net[]     = $inc - $exp;

    //         $cursor->addMonth();
    //     }

    //     return compact('labels', 'income', 'expense', 'net');
    // }

public function monthlySeries(
    int $userId,
    ?string $from = null,
    ?string $to = null,
    ?int $months = 12
): array {
    if (!Schema::hasTable('transactions')) {
        return ['labels' => [], 'income' => [], 'expense' => [], 'net' => []];
    }

    // มี from/to → ใช้ช่วงนั้น
    $start = null; $end = null;
    if ($from && $to) {
        try {
            $start = Carbon::parse($from)->startOfMonth();
            $end   = Carbon::parse($to)->endOfMonth();
            if ($start->gt($end)) { [$start, $end] = [$end, $start]; }
        } catch (\Throwable $e) {
            $start = $end = null; // ให้ไป fallback ด้านล่าง
        }
    }

    // ไม่มีหรือ parse ไม่ได้ → fallback เป็นล่าสุด N เดือน
    if (!$start || !$end) {
        $months = max(1, min(36, (int)($months ?? 12)));
        $end   = Carbon::now()->endOfMonth();
        $start = $end->copy()->startOfMonth()->subMonths($months - 1);
    }

    $rows = DB::table('transactions')
        ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as ym")
        ->selectRaw("SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) as income")
        ->selectRaw("SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense")
        ->where('user_id', $userId)
        ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
        ->groupBy('ym')
        ->orderBy('ym')
        ->get()
        ->keyBy('ym');

    $labels = $income = $expense = $net = [];
    $cursor = $start->copy()->startOfMonth();
    $last   = $end->copy()->startOfMonth();

    while ($cursor->lte($last)) {
        $ym = $cursor->format('Y-m');
        $labels[] = $cursor->format('M Y');
        $inc = (float)($rows[$ym]->income  ?? 0);
        $exp = (float)($rows[$ym]->expense ?? 0);
        $income[]  = $inc;
        $expense[] = $exp;
        $net[]     = $inc - $exp;
        $cursor->addMonth();
    }

    return compact('labels','income','expense','net');
}

    /**
     * แปลงช่วงวันที่จากสตริง → Carbon | ถ้าว่าง/ผิดรูปแบบให้คืน [null, null]
     */
    private function normalizeRange(?string $from, ?string $to): array
    {
        if (!$from || !$to) {
            return [null, null];
        }

        try {
            $f = Carbon::parse($from)->startOfDay();
            $t = Carbon::parse($to)->endOfDay();
        } catch (\Throwable $e) {
            return [null, null];
        }

        if ($f->gt($t)) {
            [$f, $t] = [$t, $f];
        }

        return [$f, $t];
    }
}
