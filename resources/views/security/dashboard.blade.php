@extends('layouts.app')
@section('title', 'Security Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Emergency alerts banner --}}
    @if($activeAlerts->count())
    <div class="bg-red-600 text-white rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="font-semibold">{{ $activeAlerts->count() }} Active Emergency Alert(s)!</span>
        </div>
        <a href="{{ route('security.alerts.index') }}" class="bg-white text-red-600 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-red-50 transition">View Alerts</a>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label'=>'Active Incidents','value'=>$stats['active_incidents'],'color'=>'border-l-4 border-red-500'],
            ['label'=>'Emergency Alerts','value'=>$stats['active_alerts'],'color'=>'border-l-4 border-orange-500'],
            ['label'=>'Scheduled Patrols','value'=>$stats['scheduled_patrols'],'color'=>'border-l-4 border-blue-500'],
            ['label'=>'Security Complaints','value'=>$stats['security_complaints'],'color'=>'border-l-4 border-yellow-500'],
        ] as $stat)
        <div class="bg-white rounded-xl border p-4 {{ $stat['color'] }}">
            <div class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent incidents --}}
        <div class="bg-white rounded-xl border p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">Recent Incidents</h3>
                <a href="{{ route('security.incidents.index') }}" class="text-indigo-600 text-sm hover:underline">View all</a>
            </div>
            @forelse($recentIncidents as $incident)
            <div class="flex items-center gap-3 py-2.5 border-b last:border-0 text-sm">
                <span class="flex-shrink-0 px-2 py-0.5 rounded text-xs {{ $incident->severityBadgeClass() }}">{{ ucfirst($incident->severity) }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ ucfirst(str_replace('_',' ',$incident->incident_type)) }}</p>
                    <p class="text-xs text-gray-500">{{ $incident->location }} · {{ $incident->occurred_at->diffForHumans() }}</p>
                </div>
                <span class="text-xs text-gray-500 capitalize flex-shrink-0">{{ $incident->status }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No incidents recorded.</p>
            @endforelse
        </div>

        {{-- Upcoming patrols --}}
        <div class="bg-white rounded-xl border p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">Upcoming Patrols</h3>
                <a href="{{ route('security.patrols.create') }}" class="text-indigo-600 text-sm hover:underline">+ Assign</a>
            </div>
            @forelse($upcomingPatrols as $patrol)
            <div class="py-2.5 border-b last:border-0 text-sm">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-800">{{ $patrol->area }}</span>
                    <span class="text-xs text-gray-500 capitalize bg-gray-100 px-2 py-0.5 rounded">{{ $patrol->status }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $patrol->assignedTo->name }} · {{ $patrol->shift_start->format('d M, h:i A') }}
                </p>
            </div>
            @empty
            <p class="text-sm text-gray-400 py-4 text-center">No patrols scheduled.</p>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <a href="{{ route('security.incidents.create') }}" class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-center hover:bg-red-100 transition">
            <div class="text-2xl mb-1">🚨</div>
            <div class="text-sm font-medium">Log Incident</div>
        </a>
        <a href="{{ route('security.patrols.create') }}" class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl p-4 text-center hover:bg-blue-100 transition">
            <div class="text-2xl mb-1">🗺</div>
            <div class="text-sm font-medium">Assign Patrol</div>
        </a>
        <a href="{{ route('security.alerts.index') }}" class="bg-orange-50 border border-orange-200 text-orange-700 rounded-xl p-4 text-center hover:bg-orange-100 transition">
            <div class="text-2xl mb-1">⚡</div>
            <div class="text-sm font-medium">Emergency Alerts</div>
        </a>
    </div>
</div>
@endsection
