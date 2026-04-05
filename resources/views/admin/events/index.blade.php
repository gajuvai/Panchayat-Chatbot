@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $events->total() }} event(s)</p>
        <a href="{{ route('admin.events.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + Create Event
        </a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Location</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Start</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">End</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">RSVPs</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $event->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $event->venue }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $event->event_date->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $event->end_date->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 text-gray-700">
                        {{ $event->rsvps_count }}
                        @if($event->max_attendees)
                            <span class="text-gray-400">/ {{ $event->max_attendees }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $event->status->badgeClass() }}">
                            {{ $event->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="text-gray-500 hover:underline text-xs">Edit</a>
                            <x-delete-form :route="route('admin.events.destroy', $event)" resource="event" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $events->links() }}
</div>
@endsection
