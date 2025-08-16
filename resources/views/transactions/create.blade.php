<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Type</label>
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="income" @selected(old('type') === 'income')>{{ __('Income') }}</option>
                                <option value="expense" @selected(old('type') === 'expense')>{{ __('Expense') }}</option>
                            </select>
                            @error('type')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">
                            @error('amount')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date') }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">
                            @error('transaction_date')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Category</label>
                            @if(count($categories))
                                <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="">{{ __('Select category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name ?? $category->title ?? $category->id }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="number" name="category_id" id="category_id" value="{{ old('category_id') }}" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">
                            @endif
                            @error('category_id')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Note</label>
                            <textarea name="note" id="note" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md shadow-sm">{{ old('note') }}</textarea>
                            @error('note')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
