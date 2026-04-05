@extends('layouts.app')
@section('title', $event->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('events.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Events</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-xs px-2 py-0.5 rounded {{ $event->status->badgeClass() }}">
                {{ $event->status->label() }}
            </span>
            <span class="text-xs text-gray-400">{{ $event->event_date->format('d M Y, h:i A') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $event->title }}</h1>

        <div class="text-sm text-gray-500 space-y-1 mb-4">
            @if($event->venue)
            <p>📍 {{ $event->venue }}</p>
            @endif
            @if($event->end_date)
            <p>🕐 Ends: {{ $event->end_date->format('d M Y, h:i A') }}</p>
            @endif
            @if($event->max_attendees)
            <p>👥 Capacity: {{ $event->max_attendees }}</p>
            @endif
            <p>Organised by {{ $event->organizer?->name ?? 'Unknown' }}</p>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 mb-6">{!! nl2br(e($event->description)) !!}</div>

        @if($event->is_rsvp_required && $event->status === 'upcoming')
        <div class="border-t pt-4">
            <h2 class="font-semibold text-gray-700 mb-3">Your RSVP</h2>

            @if($userRsvp)
            <p class="text-sm text-gray-600 mb-3">
                Current status: <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $userRsvp->status)) }}</span>
            </p>
            @endif

            <form action="{{ route('events.rsvp', $event) }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex gap-3 flex-wrap">
                    @foreach(['attending' => 'Attending', 'maybe' => 'Maybe', 'not_attending' => 'Not Attending'] as $val => $label)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="status" value="{{ $val }}"
                            {{ old('status', $userRsvp?->status) === $val ? 'checked' : '' }}
                            class="text-indigo-600">
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="flex gap-3 items-center">
                    <label class="text-sm text-gray-600">Additional guests:</label>
                    <input type="number" name="guests_count" min="0" max="10"
                        value="{{ old('guests_count', $userRsvp?->guests_count ?? 0) }}"
                        class="w-20 rounded border-gray-300 text-sm px-2 py-1">
                </div>
                <button type="submit"
                    class="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700 transition">
                    Update RSVP
                </button>
            </form>

            @if(session('success'))
            <p class="text-green-600 text-sm mt-2">{{ session('success') }}</p>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
