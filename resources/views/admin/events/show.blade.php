@extends('layouts.app')
@section('title', $event->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <x-back-link :href="route('admin.events.index')">Back to Events</x-back-link>

    <div class="bg-white rounded-xl border p-6">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <span class="text-xs px-2 py-0.5 rounded {{ $event->status->badgeClass() }}">
                    {{ $event->status->label() }}
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.events.edit', $event) }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</a>
                <x-delete-form
                    :route="route('admin.events.destroy', $event)"
                    resource="event"
                    label="Delete"
                    btn-class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition"
                    confirm="Delete this event? This cannot be undone."
                />
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $event->title }}</h1>

        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600 mb-4">
            <div>
                <span class="font-medium text-gray-700">Location:</span>
                {{ $event->venue }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Organizer:</span>
                {{ $event->organizer->name ?? '—' }}
            </div>
            <div>
                <span class="font-medium text-gray-700">Start:</span>
                {{ $event->event_date->format('d M Y, h:i A') }}
            </div>
            <div>
                <span class="font-medium text-gray-700">End:</span>
                {{ $event->end_date->format('d M Y, h:i A') }}
            </div>
            <div>
                <span class="font-medium text-gray-700">RSVPs (attending):</span>
                {{ $rsvpCount }}
                @if($event->max_attendees)
                    <span class="text-gray-400">/ {{ $event->max_attendees }} max</span>
                @endif
            </div>
        </div>

        @if($event->description)
        <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Description</p>
            <div class="text-sm text-gray-600 whitespace-pre-wrap">{{ $event->description }}</div>
        </div>
        @endif
    </div>

    {{-- RSVP List --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">RSVPs</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Resident</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Guests</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($event->rsvps as $rsvp)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $rsvp->user->name }}</td>
                    <td class="px-4 py-3">
                        @php
                            $rsvpStatusClasses = [
                                'attending'     => 'bg-green-100 text-green-700',
                                'not_attending' => 'bg-red-100 text-red-600',
                                'maybe'         => 'bg-yellow-100 text-yellow-700',
                            ];
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $rsvpStatusClasses[$rsvp->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst(str_replace('_', ' ', $rsvp->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $rsvp->guests_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $rsvp->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">No RSVPs yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
