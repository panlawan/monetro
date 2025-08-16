<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">
                Dashboard
            </h2>

            {{-- Theme switcher --}}
            <form method="POST" action="{{ route('preferences.theme') }}" class="flex items-center gap-2">
                @csrf
                <select name="theme" class="border rounded px-2 py-1 dark:bg-gray-800 dark:text-gray-100">
                    @php($pref = $pref ?? 'auto')
                    <option value="auto" @selected($pref === 'auto')>Auto</option>
                    <option value="light" @selected($pref === 'light')>Light</option>
                    <option value="dark" @selected($pref === 'dark')>Dark</option>
                </select>
                <button class="px-3 py-1 rounded bg-indigo-600 text-white">Apply</button>
            </form>
        </div>
    </x-slot>

    {{-- Alpine root - เพิ่ม key เพื่อป้องกัน double initialization --}}
    <div x-data="dashboard()" x-init="init()" @beforeunload.window="destroy()" class="py-6" key="dashboard-component">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="fromDate" class="text-sm text-gray-600 dark:text-gray-300">From</label>
                    <input id="fromDate" name="from" type="date" x-model="from"
                        class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-gray-100">
                </div>

                <div>
                    <label for="toDate" class="text-sm text-gray-600 dark:text-gray-300">To</label>
                    <input id="toDate" name="to" type="date" x-model="to"
                        class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-gray-100">
                </div>

                <div>
                    <label for="monthsSelect" class="text-sm text-gray-600 dark:text-gray-300">Months (charts)</label>
                    <select id="monthsSelect" name="months" x-model.number="months"
                        @change="loadMonthly().then(() => drawCharts())"
                        class="w-full border rounded px-3 py-2 dark:bg-gray-800 dark:text-gray-100">
                        <option :value="6">6</option>
                        <option :value="12">12</option>
                        <option :value="24">24</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button @click="applyRange()" class="px-4 py-2 rounded bg-emerald-600 text-white w-full"
                        aria-label="Refresh dashboard summary and charts">
                        Refresh
                    </button>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Income</div>
                    <div class="text-2xl font-semibold text-emerald-600" x-text="fmt(summary.total_income)"></div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Expense</div>
                    <div class="text-2xl font-semibold text-rose-600" x-text="fmt(summary.total_expense)"></div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Net</div>
                    <div class="text-2xl font-semibold" :class="summary.net_income>=0?'text-indigo-600':'text-rose-600'"
                        x-text="fmt(summary.net_income)"></div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <div class="text-gray-500 dark:text-gray-400 text-sm">Transactions</div>
                    <div class="text-2xl font-semibold text-gray-800 dark:text-gray-100"
                        x-text="summary.transaction_count"></div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">
                            Income vs Expense (<span x-text="months"></span> months)
                        </h3>
                    </div>
                    <div class="chart-container">
                        <canvas x-ref="incExpCanvas" class="w-full h-full"
                            :id="'income-chart-' + Math.random().toString(36).substr(2, 9)">
                        </canvas>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">
                        Net Income (<span x-text="months"></span> months)
                    </h3>
                    <div class="chart-container">
                        <canvas x-ref="netCanvas" class="w-full h-full"
                            :id="'net-chart-' + Math.random().toString(36).substr(2, 9)">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>