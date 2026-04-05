@extends('layouts.app')
@section('title', 'My Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome banner --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-xl p-6 text-white">
        <h2 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }}! 👋</h2>
        <p class="text-indigo-200 mt-1">Here's what's happening in your community.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label'=>'Total Complaints','value'=>$stats['total'],'color'=>'bg-blue-50 border-blue-200 text-blue-700'],
            ['label'=>'Open','value'=>$stats['open'],'color'=>'bg-yellow-50 border-yellow-200 text-yellow-700'],
            ['label'=>'In Progress','value'=>$stats['in_progress'],'color'=>'bg-orange-50 border-orange-200 text-orange-700'],
            ['label'=>'Resolved','value'=>$stats['resolved'],'color'=>'bg-green-50 border-green-200 text-green-700'],
        ] as $stat)
        <div class="bg-white rounded-xl border p-4 {{ $stat['color'] }}">
            <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
            <div class="text-sm mt-1">{{ $stat['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent complaints --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Recent Complaints</h3>
                <a href="{{ route('resident.complaints.index') }}" class="text-indigo-600 text-sm hover:underline">View all</a>
            </div>
            @forelse($recentComplaints as $complaint)
            <div class="flex items-center gap-3 py-3 border-b last:border-0">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm text-gray-800 truncate">{{ $complaint->title }}</div>
                    <div class="text-xs text-gray-500">{{ $complaint->category?->name }} · {{ $complaint->complaint_number }}</div>
                </div>
                <span class="text-xs px-2 py-1 rounded-full {{ $complaint->status->badgeClass() }} flex-shrink-0">{{ $complaint->status->label() }}</span>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                <p class="text-sm">No complaints yet.</p>
                <a href="{{ route('resident.complaints.create') }}" class="text-indigo-600 text-sm mt-2 inline-block hover:underline">File your first complaint</a>
            </div>
            @endforelse
        </div>

        {{-- Right column --}}
        <div class="space-y-4">
            {{-- Quick actions --}}
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('resident.complaints.create') }}" class="flex items-center gap-2 w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        File New Complaint
                    </a>
                    <a href="{{ route('chat.index') }}" class="flex items-center gap-2 w-full bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Open Chatbot
                    </a>
                </div>
            </div>

            {{-- Announcements --}}
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800">Announcements</h3>
                    <a href="{{ route('announcements.index') }}" class="text-indigo-600 text-xs hover:underline">See all</a>
                </div>
                @forelse($announcements as $ann)
                <div class="mb-3 pb-3 border-b last:border-0 last:mb-0 last:pb-0">
                    <span class="text-xs px-2 py-0.5 rounded {{ $ann->typeBadgeClass() }}">{{ ucfirst($ann->type) }}</span>
                    <p class="text-sm font-medium text-gray-800 mt-1">{{ $ann->title }}</p>
                    <p class="text-xs text-gray-500">{{ $ann->published_at?->diffForHumans() }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-400">No announcements.</p>
                @endforelse
            </div>

            {{-- Upcoming events --}}
            @if($upcomingEvents->count())
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Upcoming Events</h3>
                @foreach($upcomingEvents as $event)
                <div class="mb-2">
                    <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                    <p class="text-xs text-gray-500">📅 {{ $event->event_date->format('d M, h:i A') }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
