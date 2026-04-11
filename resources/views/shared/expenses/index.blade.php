@extends('layouts.app')
@section('title', 'Community Budget')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Community Budget</h1>
            <p class="text-sm text-gray-500 mt-0.5">See how community funds are spent.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="year" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.expenses.index') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Manage Expenses
            </a>
            @endif
        </div>
    </div>

    {{-- Year total --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl p-6 text-white">
        <p class="text-indigo-200 text-sm">Total Expenses — {{ $year }}</p>
        <p class="text-4xl font-bold mt-1">Rs. {{ number_format($yearTotal, 0) }}</p>
        <p class="text-indigo-200 text-sm mt-1">
            {{ $recentExpenses->count() > 0 ? 'Avg Rs. ' . number_format($yearTotal / 12, 0) . '/month' : 'No expenses logged yet' }}
        </p>
    </div>

    @if($yearTotal > 0)
    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Monthly Bar Chart --}}
        <div class="bg-white rounded-xl border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Monthly Spend ({{ $year }})</h2>
            <canvas id="monthlyChart" height="200"></canvas>
        </div>

        {{-- Category Doughnut --}}
        <div class="bg-white rounded-xl border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">By Category</h2>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Category Breakdown</h2>
        </div>
        <div class="divide-y">
            @foreach($categoryTotals as $cat => $total)
            @php $pct = $yearTotal > 0 ? round($total / $yearTotal * 100, 1) : 0; @endphp
            <div class="px-5 py-3 flex items-center gap-4">
                <span class="text-lg w-6 text-center">{{ (new App\Models\Expense(['category' => $cat]))->category_icon }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $cat)) }}</span>
                        <span class="text-sm text-gray-600">Rs. {{ number_format($total, 0) }} <span class="text-gray-400 text-xs">({{ $pct }}%)</span></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Expenses --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Recent Expenses</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50/50">
                <tr>
                    <th class="text-left px-5 py-2.5 font-medium text-gray-500 text-xs">Date</th>
                    <th class="text-left px-5 py-2.5 font-medium text-gray-500 text-xs">Title</th>
                    <th class="text-left px-5 py-2.5 font-medium text-gray-500 text-xs">Category</th>
                    <th class="text-right px-5 py-2.5 font-medium text-gray-500 text-xs">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($recentExpenses as $expense)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">
                        {{ $expense->title }}
                        @if($expense->vendor)<br><span class="text-xs text-gray-400">{{ $expense->vendor }}</span>@endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ $expense->category_icon }} {{ $expense->category_label }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                        Rs. {{ number_format($expense->amount, 0) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @else
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">📊</div>
        <p class="text-gray-500 text-sm">No expenses logged for {{ $year }} yet.</p>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.expenses.index') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Log the first expense</a>
        @endif
    </div>
    @endif

</div>

@if($yearTotal > 0)
@php
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $monthlyValues = array_values($monthlyTotals);
    $catLabels = array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($categoryTotals));
    $catValues = array_values($categoryTotals);
    $chartColors = ['#6366f1','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Monthly bar chart
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Rs.',
                data: @json($monthlyValues),
                backgroundColor: '#6366f1',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rs. ' + v.toLocaleString() } }
            }
        }
    });

    // Category doughnut
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: @json($catLabels),
            datasets: [{
                data: @json($catValues),
                backgroundColor: @json($chartColors),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rs. ' + ctx.parsed.toLocaleString() + ' (' + Math.round(ctx.parsed / @json($yearTotal) * 100) + '%)'
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
