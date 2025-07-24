<?php
// app/Http/Controllers/AccountController.php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();
        
        $accounts = Account::forUser($user->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // คำนวณยอดรวมแต่ละประเภท
        $totalsByType = $accounts->groupBy('type')->map(function ($accounts) {
            return $accounts->sum('balance');
        });

        $netWorth = $accounts->whereNotIn('type', ['credit_card', 'loan'])->sum('balance') 
                  - $accounts->whereIn('type', ['credit_card', 'loan'])->sum('balance');

        return view('finance.accounts.index', compact('accounts', 'totalsByType', 'netWorth'));
    }

    public function create(): View
    {
        return view('finance.accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,savings,checking,credit_card,investment,crypto,loan,asset',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'balance' => 'required|numeric',
            'credit_limit' => 'nullable|numeric',
            'currency' => 'string|size:3',
            'color' => 'string|size:7',
            'icon' => 'string|max:50',
            'description' => 'nullable|string'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['sort_order'] = Account::forUser(auth()->id())->max('sort_order') + 1;

        Account::create($validated);

        return redirect()->route('finance.accounts.index')
            ->with('success', 'บัญชีถูกสร้างเรียบร้อยแล้ว');
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        $transactions = Transaction::where('account_id', $account->id)
            ->orWhere('to_account_id', $account->id)
            ->with(['category', 'toAccount'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        // สถิติ 30 วันล่าสุด
        $last30Days = [
            'income' => Transaction::where('account_id', $account->id)
                ->whereIn('transaction_type', ['income', 'interest', 'dividend'])
                ->where('transaction_date', '>=', now()->subDays(30))
                ->sum('amount'),
            'expense' => Transaction::where('account_id', $account->id)
                ->whereIn('transaction_type', ['expense', 'investment_buy'])
                ->where('transaction_date', '>=', now()->subDays(30))
                ->sum('amount')
        ];

        return view('finance.accounts.show', compact('account', 'transactions', 'last30Days'));
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);
        return view('finance.accounts.edit', compact('account'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,savings,checking,credit_card,investment,crypto,loan,asset',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric',
            'currency' => 'string|size:3',
            'color' => 'string|size:7',
            'icon' => 'string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $account->update($validated);

        return redirect()->route('finance.accounts.show', $account)
            ->with('success', 'บัญชีถูกอัปเดตเรียบร้อยแล้ว');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        if ($account->transactions()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบบัญชีที่มีธุรกรรมได้');
        }

        $account->delete();

        return redirect()->route('finance.accounts.index')
            ->with('success', 'บัญชีถูกลบเรียบร้อยแล้ว');
    }
}

// app/Http/Controllers/AssetController.php
namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();
        
        $assets = Asset::forUser($user->id)
            ->active()
            ->with('account')
            ->orderBy('current_value', 'desc')
            ->get();

        // สรุปตามประเภท
        $assetsByType = $assets->groupBy('type')->map(function ($assets) {
            return [
                'count' => $assets->count(),
                'total_purchase' => $assets->sum('purchase_price'),
                'total_current' => $assets->sum('current_value'),
                'total_gain_loss' => $assets->sum(function ($asset) {
                    return $asset->current_value - $asset->purchase_price;
                })
            ];
        });

        $totalValue = $assets->sum('current_value');
        $totalGainLoss = $assets->sum(function ($asset) {
            return $asset->current_value - $asset->purchase_price;
        });

        return view('finance.assets.index', compact('assets', 'assetsByType', 'totalValue', 'totalGainLoss'));
    }

    public function create(): View
    {
        $accounts = Account::forUser(auth()->id())->active()->get();
        return view('finance.assets.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:property,vehicle,jewelry,stock,bond,mutual_fund,crypto,gold,art,electronics,other',
            'account_id' => 'nullable|exists:accounts,id',
            'purchase_price' => 'required|numeric|min:0',
            'current_value' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['valuation_date'] = now();

        Asset::create($validated);

        return redirect()->route('finance.assets.index')
            ->with('success', 'ทรัพย์สินถูกเพิ่มเรียบร้อยแล้ว');
    }

    public function show(Asset $asset): View
    {
        $this->authorize('view', $asset);

        // ประวัติการเปลี่ยนแปลงมูลค่า (จาก transactions)
        $valueHistory = $asset->transactions()
            ->whereIn('transaction_type', ['asset_purchase', 'asset_sale'])
            ->orderBy('transaction_date', 'desc')
            ->get();

        return view('finance.assets.show', compact('asset', 'valueHistory'));
    }

    public function edit(Asset $asset): View
    {
        $this->authorize('update', $asset);
        $accounts = Account::forUser(auth()->id())->active()->get();
        return view('finance.assets.edit', compact('asset', 'accounts'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:property,vehicle,jewelry,stock,bond,mutual_fund,crypto,gold,art,electronics,other',
            'account_id' => 'nullable|exists:accounts,id',
            'current_value' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json',
            'is_active' => 'boolean'
        ]);

        // อัปเดตวันที่ประเมินมูลค่าถ้ามูลค่าเปลี่ยน
        if ($validated['current_value'] != $asset->current_value) {
            $validated['valuation_date'] = now();
        }

        $asset->update($validated);

        return redirect()->route('finance.assets.show', $asset)
            ->with('success', 'ทรัพย์สินถูกอัปเดตเรียบร้อยแล้ว');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);
        $asset->delete();

        return redirect()->route('finance.assets.index')
            ->with('success', 'ทรัพย์สินถูกลบเรียบร้อยแล้ว');
    }
}

// app/Http/Controllers/TransferController.php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
        $this->middleware('auth');
    }

    public function create(): View
    {
        $accounts = Account::forUser(auth()->id())
            ->active()
            ->orderBy('name')
            ->get();

        return view('finance.transfers.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'required|date'
        ]);

        try {
            $this->transferService->createTransfer(
                $validated['from_account_id'],
                $validated['to_account_id'],
                $validated['amount'],
                $validated['description'] ?? 'Transfer',
                $validated['transaction_date']
            );

            return redirect()->route('finance.accounts.index')
                ->with('success', 'โอนเงินเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}

// app/Services/TransferService.php
namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function createTransfer(
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        string $description = 'Transfer',
        string $date = null
    ): array {
        $date = $date ?? now()->format('Y-m-d');
        
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $description, $date) {
            // ตรวจสอบบัญชี
            $fromAccount = Account::forUser(auth()->id())->findOrFail($fromAccountId);
            $toAccount = Account::forUser(auth()->id())->findOrFail($toAccountId);

            // ตรวจสอบยอดเงิน (สำหรับบัญชีที่ไม่ใช่เครดิต)
            if (!in_array($fromAccount->type, ['credit_card']) && $fromAccount->balance < $amount) {
                throw new \Exception('ยอดเงินในบัญชีไม่เพียงพอ');
            }

            // สร้าง Transaction ออก (จากบัญชี)
            $outTransaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'transaction_type' => 'transfer',
                'amount' => $amount,
                'description' => $description . ' (โอนออก)',
                'transaction_date' => $date
            ]);

            // สร้าง Transaction เข้า (ไปบัญชี)
            $inTransaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $toAccountId,
                'to_account_id' => $fromAccountId,
                'transaction_type' => 'transfer',
                'amount' => $amount,
                'description' => $description . ' (โอนเข้า)',
                'transaction_date' => $date
            ]);

            // อัปเดตยอดเงินในบัญชี
            $fromAccount->updateBalance();
            $toAccount->updateBalance();

            return [
                'out_transaction' => $outTransaction,
                'in_transaction' => $inTransaction
            ];
        });
    }
}

// app/Http/Controllers/InvestmentController.php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Asset;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvestmentController extends Controller
{
    public function __construct(
        private InvestmentService $investmentService
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();
        
        // รายการการลงทุน (จากทรัพย์สินประเภทการลงทุน)
        $investments = Asset::forUser($user->id)
            ->whereIn('type', ['stock', 'bond', 'mutual_fund', 'crypto'])
            ->with('account')
            ->get();

        // สรุปการลงทุน
        $investmentSummary = [
            'total_invested' => $investments->sum('purchase_price'),
            'current_value' => $investments->sum('current_value'),
            'total_gain_loss' => $investments->sum(function ($inv) {
                return $inv->current_value - $inv->purchase_price;
            }),
            'dividend_ytd' => Transaction::forUser($user->id)
                ->where('transaction_type', 'dividend')
                ->whereYear('transaction_date', now()->year)
                ->sum('amount')
        ];

        // เงินปันผลรายเดือน (12 เดือนล่าสุด)
        $monthlyDividends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = Transaction::forUser($user->id)
                ->where('transaction_type', 'dividend')
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
            
            $monthlyDividends[] = [
                'month' => $date->format('M Y'),
                'amount' => $amount
            ];
        }

        return view('finance.investments.index', compact(
            'investments', 'investmentSummary', 'monthlyDividends'
        ));
    }

    public function buy(): View
    {
        $accounts = Account::forUser(auth()->id())
            ->whereIn('type', ['cash', 'savings', 'checking', 'investment'])
            ->active()
            ->get();

        return view('finance.investments.buy', compact('accounts'));
    }

    public function storeBuy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'symbol' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:stock,bond,mutual_fund,crypto',
            'quantity' => 'required|numeric|min:0.0001',
            'price_per_unit' => 'required|numeric|min:0.01',
            'fees' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date'
        ]);

        try {
            $this->investmentService->buyInvestment($validated);

            return redirect()->route('finance.investments.index')
                ->with('success', 'ซื้อการลงทุนเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function sell(): View
    {
        $investments = Asset::forUser(auth()->id())
            ->whereIn('type', ['stock', 'bond', 'mutual_fund', 'crypto'])
            ->active()
            ->get();

        $accounts = Account::forUser(auth()->id())
            ->whereIn('type', ['cash', 'savings', 'checking', 'investment'])
            ->active()
            ->get();

        return view('finance.investments.sell', compact('investments', 'accounts'));
    }

    public function storeSell(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'account_id' => 'required|exists:accounts,id',
            'quantity' => 'required|numeric|min:0.0001',
            'price_per_unit' => 'required|numeric|min:0.01',
            'fees' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date'
        ]);

        try {
            $this->investmentService->sellInvestment($validated);

            return redirect()->route('finance.investments.index')
                ->with('success', 'ขายการลงทุนเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function dividend(): View
    {
        $investments = Asset::forUser(auth()->id())
            ->whereIn('type', ['stock', 'mutual_fund'])
            ->active()
            ->get();

        $accounts = Account::forUser(auth()->id())
            ->whereIn('type', ['cash', 'savings', 'checking', 'investment'])
            ->active()
            ->get();

        return view('finance.investments.dividend', compact('investments', 'accounts'));
    }

    public function storeDividend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'tax_withheld' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $this->investmentService->recordDividend($validated);

            return redirect()->route('finance.investments.index')
                ->with('success', 'บันทึกเงินปันผลเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}

// app/Services/InvestmentService.php
namespace App\Services;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class InvestmentService
{
    public function buyInvestment(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            $account = Account::forUser(auth()->id())->findOrFail($data['account_id']);
            $totalCost = ($data['quantity'] * $data['price_per_unit']) + ($data['fees'] ?? 0);

            // ตรวจสอบยอดเงิน
            if ($account->balance < $totalCost) {
                throw new \Exception('ยอดเงินในบัญชีไม่เพียงพอ');
            }

            // สร้างหรืออัปเดตทรัพย์สิน
            $asset = Asset::firstOrCreate([
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'type' => $data['type'],
                'metadata->symbol' => $data['symbol']
            ], [
                'account_id' => $data['account_id'],
                'purchase_price' => $data['quantity'] * $data['price_per_unit'],
                'current_value' => $data['quantity'] * $data['price_per_unit'],
                'purchase_date' => $data['transaction_date'],
                'valuation_date' => $data['transaction_date'],
                'metadata' => [
                    'symbol' => $data['symbol'],
                    'quantity' => $data['quantity'],
                    'avg_price' => $data['price_per_unit']
                ]
            ]);

            // ถ้ามีทรัพย์สินอยู่แล้ว ให้คำนวณราคาเฉลี่ยใหม่
            if (!$asset->wasRecentlyCreated) {
                $existingQuantity = $asset->metadata['quantity'] ?? 0;
                $existingAvgPrice = $asset->metadata['avg_price'] ?? 0;
                
                $newQuantity = $existingQuantity + $data['quantity'];
                $newAvgPrice = (($existingQuantity * $existingAvgPrice) + ($data['quantity'] * $data['price_per_unit'])) / $newQuantity;
                
                $asset->update([
                    'purchase_price' => $newQuantity * $newAvgPrice,
                    'current_value' => $newQuantity * $data['price_per_unit'],
                    'valuation_date' => $data['transaction_date'],
                    'metadata' => array_merge($asset->metadata, [
                        'quantity' => $newQuantity,
                        'avg_price' => $newAvgPrice
                    ])
                ]);
            }

            // บันทึก Transaction การซื้อ
            Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $data['account_id'],
                'asset_id' => $asset->id,
                'transaction_type' => 'investment_buy',
                'amount' => $data['quantity'] * $data['price_per_unit'],
                'quantity' => $data['quantity'],
                'price_per_unit' => $data['price_per_unit'],
                'symbol' => $data['symbol'],
                'description' => "ซื้อ {$data['symbol']} จำนวน {$data['quantity']} หน่วย",
                'transaction_date' => $data['transaction_date'],
                'metadata' => [
                    'fees' => $data['fees'] ?? 0,
                    'total_cost' => $totalCost
                ]
            ]);

            // บันทึกค่าธรรมเนียม (ถ้ามี)
            if (($data['fees'] ?? 0) > 0) {
                $feeCategory = Category::firstOrCreate([
                    'user_id' => auth()->id(),
                    'name' => 'ค่าธรรมเนียมการลงทุน',
                    'type' => 'expense'
                ]);

                Transaction::create([
                    'user_id' => auth()->id(),
                    'account_id' => $data['account_id'],
                    'category_id' => $feeCategory->id,
                    'transaction_type' => 'expense',
                    'amount' => $data['fees'],
                    'description' => "ค่าธรรมเนียมซื้อ {$data['symbol']}",
                    'transaction_date' => $data['transaction_date']
                ]);
            }

            // อัปเดตยอดเงินในบัญชี
            $account->updateBalance();

            return $asset;
        });
    }

    public function sellInvestment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $asset = Asset::forUser(auth()->id())->findOrFail($data['asset_id']);
            $account = Account::forUser(auth()->id())->findOrFail($data['account_id']);
            
            $currentQuantity = $asset->metadata['quantity'] ?? 0;
            $avgPrice = $asset->metadata['avg_price'] ?? 0;

            // ตรวจสอบจำนวนที่ขาย
            if ($data['quantity'] > $currentQuantity) {
                throw new \Exception('จำนวนที่ขายมากกว่าที่มีอยู่');
            }

            $saleAmount = $data['quantity'] * $data['price_per_unit'];
            $costBasis = $data['quantity'] * $avgPrice;
            $capitalGainLoss = $saleAmount - $costBasis;
            $netAmount = $saleAmount - ($data['fees'] ?? 0);

            // บันทึก Transaction การขาย
            $sellTransaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $data['account_id'],
                'asset_id' => $asset->id,
                'transaction_type' => 'investment_sell',
                'amount' => $saleAmount,
                'quantity' => $data['quantity'],
                'price_per_unit' => $data['price_per_unit'],
                'symbol' => $asset->metadata['symbol'] ?? '',
                'description' => "ขาย {$asset->metadata['symbol']} จำนวน {$data['quantity']} หน่วย",
                'transaction_date' => $data['transaction_date'],
                'metadata' => [
                    'fees' => $data['fees'] ?? 0,
                    'cost_basis' => $costBasis,
                    'capital_gain_loss' => $capitalGainLoss
                ]
            ]);

            // บันทึกกำไร/ขาดทุน
            if ($capitalGainLoss != 0) {
                $gainLossCategory = Category::firstOrCreate([
                    'user_id' => auth()->id(),
                    'name' => $capitalGainLoss > 0 ? 'กำไรจากการลงทุน' : 'ขาดทุนจากการลงทุน',
                    'type' => $capitalGainLoss > 0 ? 'income' : 'expense'
                ]);

                Transaction::create([
                    'user_id' => auth()->id(),
                    'account_id' => $data['account_id'],
                    'category_id' => $gainLossCategory->id,
                    'asset_id' => $asset->id,
                    'transaction_type' => $capitalGainLoss > 0 ? 'capital_gain' : 'capital_loss',
                    'amount' => abs($capitalGainLoss),
                    'description' => ($capitalGainLoss > 0 ? 'กำไร' : 'ขาดทุน') . "จากการขาย {$asset->metadata['symbol']}",
                    'transaction_date' => $data['transaction_date']
                ]);
            }

            // อัปเดตทรัพย์สิน
            $newQuantity = $currentQuantity - $data['quantity'];
            if ($newQuantity <= 0) {
                $asset->update(['is_active' => false]);
            } else {
                $asset->update([
                    'current_value' => $newQuantity * $data['price_per_unit'],
                    'valuation_date' => $data['transaction_date'],
                    'metadata' => array_merge($asset->metadata, [
                        'quantity' => $newQuantity
                    ])
                ]);
            }

            // อัปเดตยอดเงินในบัญชี
            $account->updateBalance();

            return [
                'transaction' => $sellTransaction,
                'capital_gain_loss' => $capitalGainLoss,
                'net_amount' => $netAmount
            ];
        });
    }

    public function recordDividend(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $asset = Asset::forUser(auth()->id())->findOrFail($data['asset_id']);
            $account = Account::forUser(auth()->id())->findOrFail($data['account_id']);
            
            $dividendCategory = Category::firstOrCreate([
                'user_id' => auth()->id(),
                'name' => 'เงินปันผล',
                'type' => 'income'
            ]);

            // บันทึกเงินปันผล
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'account_id' => $data['account_id'],
                'category_id' => $dividendCategory->id,
                'asset_id' => $asset->id,
                'transaction_type' => 'dividend',
                'amount' => $data['amount'],
                'symbol' => $asset->metadata['symbol'] ?? '',
                'description' => $data['description'] ?? "เงินปันผลจาก {$asset->name}",
                'transaction_date' => $data['transaction_date'],
                'metadata' => [
                    'tax_withheld' => $data['tax_withheld'] ?? 0
                ]
            ]);

            // บันทึกภาษีหัก ณ ที่จ่าย (ถ้ามี)
            if (($data['tax_withheld'] ?? 0) > 0) {
                $taxCategory = Category::firstOrCreate([
                    'user_id' => auth()->id(),
                    'name' => 'ภาษีหัก ณ ที่จ่าย',
                    'type' => 'expense'
                ]);

                Transaction::create([
                    'user_id' => auth()->id(),
                    'account_id' => $data['account_id'],
                    'category_id' => $taxCategory->id,
                    'transaction_type' => 'expense',
                    'amount' => $data['tax_withheld'],
                    'description' => "ภาษีหัก ณ ที่จ่าย - เงินปันผล {$asset->name}",
                    'transaction_date' => $data['transaction_date']
                ]);
            }

            // อัปเดตยอดเงินในบัญชี
            $account->updateBalance();

            return $transaction;
        });
    }
}