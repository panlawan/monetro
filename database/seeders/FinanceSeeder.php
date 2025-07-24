<?php
// database/seeders/FinanceSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Asset;
use App\Models\Transaction;

class FinanceSeeder extends Seeder
{
    public function run()
    {
        // สร้าง Demo User (ถ้ายังไม่มี)
        $user = User::firstOrCreate([
            'email' => 'demo@example.com'
        ], [
            'name' => 'Demo User',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ]);

        // สร้างบัญชีตัวอย่าง
        $accounts = [
            [
                'name' => 'เงินสด',
                'type' => 'cash',
                'balance' => 5000,
                'color' => '#10B981',
                'icon' => 'wallet'
            ],
            [
                'name' => 'กสิกรไทย - ออมทรัพย์',
                'type' => 'savings',
                'bank_name' => 'ธนาคารกสิกรไทย',
                'account_number' => '123-4-56789-0',
                'balance' => 150000,
                'color' => '#059669',
                'icon' => 'university'
            ],
            [
                'name' => 'SCB - กระแสรายวัน',
                'type' => 'checking',
                'bank_name' => 'ธนาคารไทยพาณิชย์',
                'account_number' => '987-6-54321-0',
                'balance' => 25000,
                'color' => '#7C3AED',
                'icon' => 'credit-card'
            ],
            [
                'name' => 'KTC - บัตรเครดิต',
                'type' => 'credit_card',
                'bank_name' => 'บัตรกรุงไทย',
                'account_number' => '**** **** **** 1234',
                'balance' => 8500, // หนี้ที่ค้าง
                'credit_limit' => 50000,
                'color' => '#EF4444',
                'icon' => 'credit-card'
            ],
            [
                'name' => 'บัญชีลงทุน - Bitkub',
                'type' => 'investment',
                'balance' => 75000,
                'color' => '#F59E0B',
                'icon' => 'chart-line'
            ]
        ];

        foreach ($accounts as $index => $accountData) {
            $accountData['user_id'] = $user->id;
            $accountData['sort_order'] = $index + 1;
            Account::create($accountData);
        }

        // สร้างหมวดหมู่เพิ่มเติม
        $categories = [
            // Income Categories
            ['name' => 'เงินเดือน', 'type' => 'income', 'color' => '#10B981', 'icon' => 'briefcase'],
            ['name' => 'โบนัส', 'type' => 'income', 'color' => '#059669', 'icon' => 'gift'],
            ['name' => 'เงินปันผล', 'type' => 'income', 'color' => '#34D399', 'icon' => 'chart-line'],
            ['name' => 'ดอกเบี้ย', 'type' => 'income', 'color' => '#6EE7B7', 'icon' => 'percentage'],
            ['name' => 'รายได้เสริม', 'type' => 'income', 'color' => '#A7F3D0', 'icon' => 'hand-holding-usd'],
            
            // Expense Categories
            ['name' => 'อาหารการกิน', 'type' => 'expense', 'color' => '#EF4444', 'icon' => 'utensils'],
            ['name' => 'ค่าที่พัก', 'type' => 'expense', 'color' => '#DC2626', 'icon' => 'home'],
            ['name' => 'การเดินทาง', 'type' => 'expense', 'color' => '#B91C1C', 'icon' => 'car'],
            ['name' => 'ความบันเทิง', 'type' => 'expense', 'color' => '#991B1B', 'icon' => 'film'],
            ['name' => 'สุขภาพ', 'type' => 'expense', 'color' => '#F97316', 'icon' => 'heartbeat'],
            ['name' => 'การศึกษา', 'type' => 'expense', 'color' => '#EA580C', 'icon' => 'graduation-cap'],
            ['name' => 'ค่าธรรมเนียมการลงทุน', 'type' => 'expense', 'color' => '#DC2626', 'icon' => 'chart-line'],
            ['name' => 'ภาษีหัก ณ ที่จ่าย', 'type' => 'expense', 'color' => '#B91C1C', 'icon' => 'receipt'],
            
            // Investment Categories
            ['name' => 'กำไรจากการลงทุน', 'type' => 'income', 'color' => '#059669', 'icon' => 'trending-up'],
            ['name' => 'ขาดทุนจากการลงทุน', 'type' => 'expense', 'color' => '#DC2626', 'icon' => 'trending-down'],
        ];

        foreach ($categories as $index => $categoryData) {
            $categoryData['user_id'] = $user->id;
            $categoryData['sort_order'] = $index + 1;
            Category::create($categoryData);
        }

        // สร้างทรัพย์สินตัวอย่าง
        $assets = [
            [
                'name' => 'คอนโดมิเนียม - ลุมพินี',
                'type' => 'property',
                'purchase_price' => 2500000,
                'current_value' => 2800000,
                'purchase_date' => '2020-01-15',
                'location' => 'กรุงเทพมหานคร',
                'description' => 'คอนโด 1 ห้องนอน ขนาด 35 ตร.ม.'
            ],
            [
                'name' => 'รถยนต์ - Honda Civic',
                'type' => 'vehicle',
                'purchase_price' => 800000,
                'current_value' => 600000,
                'purchase_date' => '2019-06-01',
                'description' => 'Honda Civic 2019 สีขาว',
                'metadata' => [
                    'brand' => 'Honda',
                    'model' => 'Civic',
                    'year' => 2019,
                    'license_plate' => 'กข-1234'
                ]
            ],
            [
                'name' => 'PTT - บริษัท ปตท. จำกัด (มหาชน)',
                'type' => 'stock',
                'purchase_price' => 50000,
                'current_value' => 55000,
                'purchase_date' => '2023-01-01',
                'metadata' => [
                    'symbol' => 'PTT',
                    'quantity' => 1000,
                    'avg_price' => 50
                ]
            ],
            [
                'name' => 'Bitcoin',
                'type' => 'crypto',
                'purchase_price' => 100000,
                'current_value' => 120000,
                'purchase_date' => '2023-06-15',
                'metadata' => [
                    'symbol' => 'BTC',
                    'quantity' => 0.05,
                    'avg_price' => 2000000
                ]
            ]
        ];

        $investmentAccount = Account::where('user_id', $user->id)
            ->where('type', 'investment')
            ->first();

        foreach ($assets as $assetData) {
            $assetData['user_id'] = $user->id;
            $assetData['valuation_date'] = now();
            
            if (in_array($assetData['type'], ['stock', 'crypto'])) {
                $assetData['account_id'] = $investmentAccount->id;
            }
            
            Asset::create($assetData);
        }

        // สร้างธุรกรรมตัวอย่าง
        $this->createSampleTransactions($user);
    }

    private function createSampleTransactions(User $user)
    {
        $cashAccount = Account::where('user_id', $user->id)->where('type', 'cash')->first();
        $savingsAccount = Account::where('user_id', $user->id)->where('type', 'savings')->first();
        $investmentAccount = Account::where('user_id', $user->id)->where('type', 'investment')->first();
        
        $salaryCategory = Category::where('user_id', $user->id)->where('name', 'เงินเดือน')->first();
        $foodCategory = Category::where('user_id', $user->id)->where('name', 'อาหารการกิน')->first();
        $dividendCategory = Category::where('user_id', $user->id)->where('name', 'เงินปันผล')->first();

        $transactions = [
            // เงินเดือนประจำเดือน
            [
                'account_id' => $savingsAccount->id,
                'category_id' => $salaryCategory->id,
                'transaction_type' => 'income',
                'amount' => 45000,
                'description' => 'เงินเดือนประจำเดือน',
                'transaction_date' => now()->subMonth()->format('Y-m-d')
            ],
            [
                'account_id' => $savingsAccount->id,
                'category_id' => $salaryCategory->id,
                'transaction_type' => 'income',
                'amount' => 45000,
                'description' => 'เงินเดือนประจำเดือน',
                'transaction_date' => now()->format('Y-m-d')
            ],
            
            // ค่าใช้จ่ายประจำวัน
            [
                'account_id' => $cashAccount->id,
                'category_id' => $foodCategory->id,
                'transaction_type' => 'expense',
                'amount' => 350,
                'description' => 'อาหารเที่ยง',
                'transaction_date' => now()->subDays(1)->format('Y-m-d')
            ],
            [
                'account_id' => $cashAccount->id,
                'category_id' => $foodCategory->id,
                'transaction_type' => 'expense',
                'amount' => 280,
                'description' => 'อาหารเย็น',
                'transaction_date' => now()->format('Y-m-d')
            ],
            
            // การโอนเงิน
            [
                'account_id' => $savingsAccount->id,
                'to_account_id' => $cashAccount->id,
                'transaction_type' => 'transfer',
                'amount' => 5000,
                'description' => 'โอนเงินไปใช้จ่าย',
                'transaction_date' => now()->subDays(3)->format('Y-m-d')
            ],
            [
                'account_id' => $cashAccount->id,
                'to_account_id' => $savingsAccount->id,
                'transaction_type' => 'transfer',
                'amount' => 5000,
                'description' => 'โอนเงินเข้าออมทรัพย์',
                'transaction_date' => now()->subDays(3)->format('Y-m-d')
            ],
            
            // เงินปันผล
            [
                'account_id' => $investmentAccount->id,
                'category_id' => $dividendCategory->id,
                'transaction_type' => 'dividend',
                'amount' => 1500,
                'symbol' => 'PTT',
                'description' => 'เงินปันผล PTT',
                'transaction_date' => now()->subDays(7)->format('Y-m-d')
            ]
        ];

        foreach ($transactions as $transactionData) {
            $transactionData['user_id'] = $user->id;
            Transaction::create($transactionData);
        }

        // อัปเดตยอดเงินในบัญชีทั้งหมด
        Account::where('user_id', $user->id)->get()->each(function ($account) {
            $account->updateBalance();
        });
    }
}

// database/migrations/2024_create_net_worth_snapshots_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('net_worth_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('snapshot_date');
            $table->decimal('total_assets', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);
            $table->decimal('net_worth', 15, 2)->default(0);
            $table->json('breakdown')->nullable(); // รายละเอียดตามประเภทบัญชี
            $table->timestamps();
            
            $table->unique(['user_id', 'snapshot_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('net_worth_snapshots');
    }
};

// app/Models/NetWorthSnapshot.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetWorthSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'snapshot_date', 'total_assets', 
        'total_liabilities', 'net_worth', 'breakdown'
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'total_assets' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'net_worth' => 'decimal:2',
        'breakdown' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createSnapshot(int $userId, string $date = null): self
    {
        $date = $date ?? now()->format('Y-m-d');
        $user = User::findOrFail($userId);

        // คำนวณทรัพย์สินและหนี้สิน
        $accounts = Account::forUser($userId)->active()->get();
        $assets = Asset::forUser($userId)->active()->get();

        $assetAccounts = $accounts->whereNotIn('type', ['credit_card', 'loan']);
        $liabilityAccounts = $accounts->whereIn('type', ['credit_card', 'loan']);

        $totalAssets = $assetAccounts->sum('balance') + $assets->sum('current_value');
        $totalLiabilities = $liabilityAccounts->sum('balance');
        $netWorth = $totalAssets - $totalLiabilities;

        // รายละเอียดตามประเภท
        $breakdown = [
            'accounts' => $accounts->groupBy('type')->map(function ($accounts) {
                return $accounts->sum('balance');
            })->toArray(),
            'assets' => $assets->groupBy('type')->map(function ($assets) {
                return $assets->sum('current_value');
            })->toArray(),
            'total_accounts' => $assetAccounts->sum('balance'),
            'total_physical_assets' => $assets->sum('current_value'),
        ];

        return self::updateOrCreate(
            ['user_id' => $userId, 'snapshot_date' => $date],
            [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'net_worth' => $netWorth,
                'breakdown' => $breakdown
            ]
        );
    }
}

// app/Console/Commands/CreateNetWorthSnapshots.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\NetWorthSnapshot;

class CreateNetWorthSnapshots extends Command
{
    protected $signature = 'finance:net-worth-snapshots {--date=}';
    protected $description = 'Create net worth snapshots for all users';

    public function handle()
    {
        $date = $this->option('date') ?? now()->format('Y-m-d');
        
        $users = User::whereHas('accounts')->get();
        
        $this->info("Creating net worth snapshots for {$users->count()} users on {$date}");
        
        $progressBar = $this->output->createProgressBar($users->count());
        
        foreach ($users as $user) {
            NetWorthSnapshot::createSnapshot($user->id, $date);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info('Net worth snapshots created successfully!');
    }
}

// app/Console/Kernel.php - เพิ่มใน schedule method
protected function schedule(Schedule $schedule)
{
    // สร้าง snapshot มูลค่าสุทธิทุกวัน
    $schedule->command('finance:net-worth-snapshots')
             ->daily()
             ->at('23:59');
}

// app/Http/Controllers/FinanceController.php - เพิ่ม method ใหม่
public function netWorth(): View
{
    $user = auth()->user();
    
    // Snapshot ล่าสุด
    $latestSnapshot = NetWorthSnapshot::where('user_id', $user->id)
        ->latest('snapshot_date')
        ->first();
    
    // ถ้าไม่มี snapshot วันนี้ ให้สร้างใหม่
    if (!$latestSnapshot || $latestSnapshot->snapshot_date->format('Y-m-d') !== now()->format('Y-m-d')) {
        $latestSnapshot = NetWorthSnapshot::createSnapshot($user->id);
    }
    
    // ข้อมูล 12 เดือนล่าสุด
    $monthlySnapshots = NetWorthSnapshot::where('user_id', $user->id)
        ->where('snapshot_date', '>=', now()->subYear())
        ->orderBy('snapshot_date')
        ->get()
        ->groupBy(function ($snapshot) {
            return $snapshot->snapshot_date->format('Y-m');
        })
        ->map(function ($snapshots) {
            return $snapshots->last(); // เอาวันสุดท้ายของเดือน
        })
        ->values();
    
    // คำนวณการเปลี่ยนแปลง
    $previousSnapshot = NetWorthSnapshot::where('user_id', $user->id)
        ->where('snapshot_date', '<', now()->format('Y-m-d'))
        ->latest('snapshot_date')
        ->first();
    
    $changes = [
        'amount' => $previousSnapshot ? 
            $latestSnapshot->net_worth - $previousSnapshot->net_worth : 0,
        'percentage' => $previousSnapshot && $previousSnapshot->net_worth != 0 ? 
            (($latestSnapshot->net_worth - $previousSnapshot->net_worth) / abs($previousSnapshot->net_worth)) * 100 : 0
    ];
    
    // รายละเอียดตามประเภท
    $breakdown = $latestSnapshot->breakdown;
    
    return view('finance.net-worth', compact(
        'latestSnapshot', 'monthlySnapshots', 'changes', 'breakdown'
    ));
}

// resources/views/finance/net-worth.blade.php
?>
@extends('layouts.app')

@section('title', 'มูลค่าสุทธิ')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">มูลค่าสุทธิ (Net Worth)</h1>
        <div class="text-right">
            <p class="text-sm text-gray-500">อัปเดตล่าสุด: {{ $latestSnapshot->snapshot_date->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- Current Net Worth -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-lg col-span-1 md:col-span-1">
            <h3 class="text-lg font-semibold mb-2">มูลค่าสุทธิปัจจุบัน</h3>
            <p class="text-4xl font-bold">฿{{ number_format($latestSnapshot->net_worth, 2) }}</p>
            @if($changes['amount'] != 0)
            <div class="mt-2 flex items-center">
                <span class="text-sm {{ $changes['amount'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                    {{ $changes['amount'] >= 0 ? '+' : '' }}฿{{ number_format($changes['amount'], 2) }}
                    ({{ number_format($changes['percentage'], 2) }}%)
                </span>
            </div>
            @endif
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">ทรัพย์สินรวม</h3>
            <p class="text-3xl font-bold text-green-600">฿{{ number_format($latestSnapshot->total_assets, 2) }}</p>
            <div class="mt-2 text-sm text-gray-500">
                <p>บัญชี: ฿{{ number_format($breakdown['total_accounts'] ?? 0, 2) }}</p>
                <p>ทรัพย์สิน: ฿{{ number_format($breakdown['total_physical_assets'] ?? 0, 2) }}</p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">หนี้สินรวม</h3>
            <p class="text-3xl font-bold text-red-600">฿{{ number_format($latestSnapshot->total_liabilities, 2) }}</p>
            <div class="mt-2 text-sm text-gray-500">
                @foreach(['credit_card' => 'บัตรเครดิต', 'loan' => 'สินเชื่อ'] as $type => $label)
                @if(isset($breakdown['accounts'][$type]))
                <p>{{ $label }}: ฿{{ number_format($breakdown['accounts'][$type], 2) }}</p>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Net Worth Trend Chart -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">แนวโน้มมูลค่าสุทธิ (12 เดือนล่าสุด)</h3>
        <canvas id="netWorthChart" width="400" height="100"></canvas>
    </div>

    <!-- Assets Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Account Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">สัดส่วนตามประเภทบัญชี</h3>
            <div class="space-y-4">
                @foreach($breakdown['accounts'] ?? [] as $type => $amount)
                @if($amount > 0)
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">{{ App\Models\Account::getTypeOptions()[$type] ?? $type }}</span>
                    <span class="font-semibold">฿{{ number_format($amount, 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" 
                         style="width: {{ $latestSnapshot->total_assets > 0 ? ($amount / $latestSnapshot->total_assets) * 100 : 0 }}%"></div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

        <!-- Asset Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">สัดส่วนตามประเภททรัพย์สิน</h3>
            <div class="space-y-4">
                @foreach($breakdown['assets'] ?? [] as $type => $amount)
                @if($amount > 0)
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">{{ App\Models\Asset::getTypeOptions()[$type] ?? $type }}</span>
                    <span class="font-semibold">฿{{ number_format($amount, 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" 
                         style="width: {{ $latestSnapshot->total_assets > 0 ? ($amount / $latestSnapshot->total_assets) * 100 : 0 }}%"></div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('netWorthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($monthlySnapshots->map(function($snapshot) { return $snapshot->snapshot_date->format('M Y'); })),
            datasets: [{
                label: 'มูลค่าสุทธิ (฿)',
                data: @json($monthlySnapshots->pluck('net_worth')),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return '฿' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'มูลค่าสุทธิ: ฿' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
