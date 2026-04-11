@extends('layouts.app')
@section('title', 'Manage Expenses')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Manage Expenses</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.expenses.export', ['year' => request('month') ? substr(request('month'), 0, 4) : now()->year]) }}"
                class="border border-green-300 text-green-700 px-4 py-2 rounded-lg text-sm hover:bg-green-50 transition">
                ⬇ Export CSV
            </a>
            <a href="{{ route('expenses.index') }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                View Dashboard
            </a>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'log-expense' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Log Expense
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search title, vendor, invoice..."
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach(App\Models\Expense::categories() as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ request('month') }}"
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.expenses.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Category</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Vendor</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Logged By</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $expense->title }}</p>
                        @if($expense->invoice_number)
                        <p class="text-xs text-gray-400">Invoice: {{ $expense->invoice_number }}</p>
                        @endif
                        @if($expense->is_recurring)
                        <span class="text-xs bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">Recurring</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $expense->category_icon }} {{ $expense->category_label }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">
                        Rs. {{ number_format($expense->amount, 2) }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $expense->vendor ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $expense->loggedBy->name }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($expense->receipt_url)
                            <a href="{{ $expense->receipt_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Receipt</a>
                            @endif
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-expense-{{ $expense->id }}' }))"
                                class="text-xs text-gray-500 hover:underline">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-expense-{{ $expense->id }}' }))"
                                class="text-xs text-red-400 hover:underline">Delete</button>
                        </div>
                    </td>
                </tr>

                {{-- Edit Modal --}}
                <x-modal name="edit-expense-{{ $expense->id }}" :show="old('_edit_id') == $expense->id && $errors->any()" maxWidth="xl">
                    <div class="bg-white rounded-xl overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                            <h2 class="text-base font-semibold text-gray-800">Edit Expense</h2>
                            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @php $isActive = old('_edit_id') == $expense->id && $errors->any(); @endphp
                        <div class="overflow-y-auto max-h-[80vh]">
                        <form action="{{ route('admin.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                            @csrf @method('PATCH')
                            <input type="hidden" name="_edit_id" value="{{ $expense->id }}">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" value="{{ $isActive ? old('title') : $expense->title }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                                    <input type="number" name="amount" value="{{ $isActive ? old('amount') : $expense->amount }}" step="0.01" min="0.01"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="expense_date" value="{{ $isActive ? old('expense_date') : $expense->expense_date->format('Y-m-d') }}"
                                        max="{{ today()->format('Y-m-d') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        @foreach(App\Models\Expense::categories() as $cat)
                                        <option value="{{ $cat }}" {{ ($isActive ? old('category') : $expense->category) === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                                    <input type="text" name="vendor" value="{{ $isActive ? old('vendor') : $expense->vendor }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice #</label>
                                    <input type="text" name="invoice_number" value="{{ $isActive ? old('invoice_number') : $expense->invoice_number }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Replace Receipt</label>
                                <input type="file" name="receipt" accept="image/*,.pdf"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div class="flex gap-3 pt-2 border-t">
                                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save</button>
                                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </x-modal>

                {{-- Delete Modal --}}
                <x-modal name="delete-expense-{{ $expense->id }}" maxWidth="sm">
                    <div class="bg-white rounded-xl overflow-hidden">
                        <div class="px-6 py-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Delete Expense</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                                <span class="font-medium">{{ $expense->title }}</span>
                                — Rs. {{ number_format($expense->amount, 2) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 px-6 pb-5">
                            <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
                            </form>
                            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                        </div>
                    </div>
                </x-modal>

                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $expenses->links() }}
</div>

{{-- Log Expense Modal --}}
<x-modal name="log-expense" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Log New Expense</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Lift annual maintenance">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-400 @enderror"
                        placeholder="0.00">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', today()->format('Y-m-d')) }}"
                        max="{{ today()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expense_date') border-red-400 @enderror">
                    @error('expense_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(App\Models\Expense::categories() as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <input type="text" name="vendor" value="{{ old('vendor') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Optional">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice #</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Optional">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="checkbox" name="is_recurring" value="1"
                            class="rounded border-gray-300 text-indigo-600"
                            {{ old('is_recurring') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Recurring expense</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Optional notes...">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="file" name="receipt" accept="image/*,.pdf"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('receipt')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Log Expense
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endsection
