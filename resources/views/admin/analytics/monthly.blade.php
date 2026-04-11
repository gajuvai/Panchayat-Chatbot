@extends('layouts.app')
@section('title', 'Monthly Trend Analytics')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Monthly Trend Comparison</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $currentYear }} vs {{ $lastYear }} complaint trends.</p>
        </div>
        <div class="flex gap-2 text-sm">
            <a href="{{ route('admin.analytics.index') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">Overview</a>
            <a href="{{ route('admin.analytics.sla') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">SLA</a>
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-medium">Monthly</span>
        </div>
    </div>

    {{-- Year comparison chart --}}
    <div class="bg-white rounded-xl border p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Complaints Filed — {{ $currentYear }} vs {{ $lastYear }}</h2>
        <canvas id="comparisonChart" height="120"></canvas>
    </div>

    {{-- Resolution rate chart --}}
    <div class="bg-white rounded-xl border p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Monthly Resolution Rate — {{ $currentYear }} (%)</h2>
        <canvas id="resolutionChart" height="100"></canvas>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-5 py-4 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Month-by-Month Breakdown</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Month</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ $currentYear }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ $lastYear }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Change</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Resolution %</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($months as $i => $month)
                @php
                    $curr   = $currentData[$i];
                    $prev   = $lastData[$i];
                    $change = $prev > 0 ? round(($curr - $prev) / $prev * 100, 1) : ($curr > 0 ? 100 : 0);
                    $res    = $resolutionByMonth[$i];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-700">{{ $month }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ $curr }}</td>
                    <td class="px-4 py-3 text-right text-gray-400">{{ $prev }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($prev > 0 || $curr > 0)
                        <span class="text-xs font-medium {{ $change > 0 ? 'text-red-500' : ($change < 0 ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $change > 0 ? '+' : '' }}{{ $change }}%
                        </span>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-gray-500">
                        @if($curr > 0){{ $res }}%@else —@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('comparisonChart'), {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [
                {
                    label: '{{ $currentYear }}',
                    data: @json($currentData),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                },
                {
                    label: '{{ $lastYear }}',
                    data: @json($lastData),
                    borderColor: '#d1d5db',
                    borderDash: [5,5],
                    tension: 0.3,
                    fill: false,
                    pointRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('resolutionChart'), {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Resolution %',
                data: @json($resolutionByMonth),
                backgroundColor: '#10b981',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
        }
    });
});
</script>
@endsection
