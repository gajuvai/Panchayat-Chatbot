@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $statCards = [
            ['label'=>'Total Complaints','value'=>$stats['total_complaints'],'color'=>'border-l-4 border-blue-500 bg-white'],
            ['label'=>'Open Complaints','value'=>$stats['open_complaints'],'color'=>'border-l-4 border-yellow-400 bg-white'],
            ['label'=>'In Progress','value'=>$stats['in_progress'],'color'=>'border-l-4 border-orange-400 bg-white'],
            ['label'=>'Resolved Today','value'=>$stats['resolved_today'],'color'=>'border-l-4 border-green-500 bg-white'],
            ['label'=>'Total Residents','value'=>$stats['total_residents'],'color'=>'border-l-4 border-indigo-500 bg-white'],
            ['label'=>'Active Events','value'=>$stats['active_events'],'color'=>'border-l-4 border-purple-400 bg-white'],
            ['label'=>'Active Polls','value'=>$stats['active_polls'],'color'=>'border-l-4 border-pink-400 bg-white'],
        ]; @endphp
        @foreach($statCards as $card)
        <div class="{{ $card['color'] }} rounded-xl shadow-sm p-4">
            <div class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Urgent complaints --}}
        @if($urgentComplaints->count())
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <h3 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Urgent Complaints ({{ $urgentComplaints->count() }})
            </h3>
            @foreach($urgentComplaints as $c)
            <div class="flex items-center justify-between py-2 border-b border-red-100 last:border-0 text-sm">
                <div>
                    <span class="font-medium text-gray-800">{{ $c->title }}</span>
                    <span class="text-xs text-gray-500 ml-1">by {{ $c->user->name }}</span>
                </div>
                <a href="{{ route('admin.complaints.show', $c) }}" class="text-red-700 font-medium hover:underline text-xs">Handle →</a>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Recent complaints --}}
        <div class="bg-white rounded-xl border p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">Recent Complaints</h3>
                <a href="{{ route('admin.complaints.index') }}" class="text-indigo-600 text-sm hover:underline">View all</a>
            </div>
            @foreach($recentComplaints->take(8) as $complaint)
            <div class="flex items-center gap-3 py-2 border-b last:border-0 text-sm">
                <div class="flex-1 min-w-0">
                    <span class="font-medium text-gray-800 truncate block">{{ $complaint->title }}</span>
                    <span class="text-xs text-gray-500">{{ $complaint->user->name }} · {{ $complaint->category?->name }}</span>
                </div>
                <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Quick links --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['href'=>route('admin.announcements.create'),'label'=>'New Announcement','icon'=>'📢','color'=>'bg-indigo-50 border-indigo-200 text-indigo-700'],
            ['href'=>route('admin.events.create'),'label'=>'Create Event','icon'=>'📅','color'=>'bg-purple-50 border-purple-200 text-purple-700'],
            ['href'=>route('admin.polls.create'),'label'=>'New Poll','icon'=>'🗳','color'=>'bg-pink-50 border-pink-200 text-pink-700'],
            ['href'=>route('admin.analytics.index'),'label'=>'View Analytics','icon'=>'📊','color'=>'bg-green-50 border-green-200 text-green-700'],
        ] as $link)
        <a href="{{ $link['href'] }}" class="border rounded-xl p-4 text-center {{ $link['color'] }} hover:shadow-sm transition">
            <div class="text-2xl mb-1">{{ $link['icon'] }}</div>
            <div class="text-sm font-medium">{{ $link['label'] }}</div>
        </a>
        @endforeach
    </div>
</div>
@endsection
