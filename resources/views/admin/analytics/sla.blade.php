@extends('layouts.app')
@section('title', 'SLA & Resolution Analytics')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">SLA & Resolution Analytics</h1>
            <p class="text-sm text-gray-500 mt-0.5">Complaint resolution time and breach tracking.</p>
        </div>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('admin.analytics.index') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">Overview</a>
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-medium">SLA</span>
            <a href="{{ route('admin.analytics.monthly') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">Monthly</a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-red-600">{{ $breaches }}</div>
            <div class="text-xs text-red-500 mt-1">SLA Breaches</div>
            <div class="text-xs text-gray-400 mt-0.5">Open complaints past deadline</div>
        </div>
        <div class="bg-white border rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-gray-800">{{ $ageBuckets['0–7 days'] }}</div>
            <div class="text-xs text-gray-500 mt-1">0–7 Days Old</div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-yellow-600">{{ $ageBuckets['7–30 days'] }}</div>
            <div class="text-xs text-yellow-600 mt-1">7–30 Days Old</div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-red-600">{{ $ageBuckets['30+ days'] }}</div>
            <div class="text-xs text-red-500 mt-1">30+ Days Old</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Avg Resolution by Category --}}
        <div class="bg-white rounded-xl border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Avg Resolution Time by Category (hours)</h2>
            @if($avgByCategory->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">No resolved complaints yet.</p>
            @else
            <canvas id="categoryChart" height="220"></canvas>
            @endif
        </div>

        {{-- Avg by Priority --}}
        <div class="bg-white rounded-xl border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Avg Resolution Time by Priority (hours)</h2>
            @if($avgByPriority->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">No resolved complaints yet.</p>
            @else
            <div class="space-y-3 mt-2">
                @foreach(['urgent' => '#ef4444', 'high' => '#f97316', 'medium' => '#f59e0b', 'low' => '#6b7280'] as $priority => $color)
                @if(isset($avgByPriority[$priority]))
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 capitalize">{{ $priority }}</span>
                        <span class="text-sm text-gray-500">{{ $avgByPriority[$priority] }}h avg</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php $maxHours = max($avgByPriority->values()->toArray() ?: [1]); @endphp
                        <div class="h-2 rounded-full" style="width: {{ min(100, ($avgByPriority[$priority] / $maxHours) * 100) }}%; background-color: {{ $color }};"></div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Age Buckets Chart --}}
    <div class="bg-white rounded-xl border p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Open Complaint Age Distribution</h2>
        <canvas id="ageBucketChart" height="80"></canvas>
    </div>
</div>

@if($avgByCategory->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: @json($avgByCategory->keys()),
            datasets: [{
                label: 'Avg hours',
                data: @json($avgByCategory->values()),
                backgroundColor: '#6366f1',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'h' } } }
        }
    });

    new Chart(document.getElementById('ageBucketChart'), {
        type: 'bar',
        data: {
            labels: @json(array_keys($ageBuckets)),
            datasets: [{
                data: @json(array_values($ageBuckets)),
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
@endif
@endsection
