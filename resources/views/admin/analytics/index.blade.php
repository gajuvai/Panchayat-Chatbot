@extends('layouts.app')
@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Community engagement and complaint statistics</p>
        <div class="flex items-center gap-2">
            <div class="flex gap-1 text-sm">
                <span class="bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg font-medium">Overview</span>
                <a href="{{ route('admin.analytics.sla') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">SLA</a>
                <a href="{{ route('admin.analytics.monthly') }}" class="border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-50">Monthly</a>
            </div>
            <a href="{{ route('admin.analytics.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">⬇ Export CSV</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Complaints by Status</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Complaints by Category</h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Complaint Trend (Last 12 Months)</h3>
        <canvas id="trendChart" height="100"></canvas>
    </div>

    <div class="bg-white rounded-xl border p-4 text-center" id="resolutionRate">
        <div class="text-4xl font-bold text-green-600" id="rateValue">-</div>
        <div class="text-sm text-gray-500 mt-1">Overall Resolution Rate</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('{{ route("admin.analytics.data") }}')
        .then(r => r.json())
        .then(data => {
            // Status doughnut
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.byStatus).map(s => s.replace('_',' ').replace(/\b\w/g, c => c.toUpperCase())),
                    datasets: [{ data: Object.values(data.byStatus), backgroundColor: ['#3b82f6','#f59e0b','#10b981','#6b7280','#ef4444'] }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });

            // Category bar
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(data.byCategory),
                    datasets: [{ label: 'Complaints', data: Object.values(data.byCategory), backgroundColor: '#6366f1' }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });

            // Trend line
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: data.trendData.map(d => d.month),
                    datasets: [{ label: 'Complaints Filed', data: data.trendData.map(d => d.count), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.4, fill: true }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            document.getElementById('rateValue').textContent = data.resolutionRate + '%';
        });
});
</script>
@endsection
