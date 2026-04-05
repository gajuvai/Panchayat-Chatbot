@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="space-y-4">
    @forelse($events as $event)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs px-2 py-0.5 rounded
                        {{ $event->status === 'upcoming' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $event->status === 'ongoing' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $event->status === 'completed' ? 'bg-gray-100 text-gray-600' : '' }}">
                        {{ ucfirst($event->status) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $event->event_date->format('d M Y, h:i A') }}</span>
                    @if($event->venue)
                        <span class="text-xs text-gray-400">· {{ $event->venue }}</span>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-800">{{ $event->title }}</h3>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ strip_tags($event->description) }}</p>
                @if($event->is_rsvp_required)
                <p class="text-xs text-indigo-600 mt-1">RSVP required
                    @if($event->rsvp_deadline)· by {{ $event->rsvp_deadline->format('d M Y') }}@endif
                </p>
                @endif
            </div>
            <a href="{{ route('events.show', $event) }}" class="flex-shrink-0 text-indigo-600 text-sm hover:underline">View →</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <p class="text-gray-400">No upcoming events.</p>
    </div>
    @endforelse
    {{ $events->links() }}
</div>
@endsection
