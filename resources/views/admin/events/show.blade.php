@extends('layouts.app')
@section('title', $event->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <x-back-link :href="route('admin.events.index')">Back to Events</x-back-link>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <span class="text-xs px-2 py-0.5 rounded {{ $event->status->badgeClass() }}">{{ $event->status->label() }}</span>
            </div>
            <div class="flex gap-2">
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-event' }))"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</button>
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-event' }))"
                    class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $event->title }}</h1>

        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600 mb-4">
            <div><span class="font-medium text-gray-700">Location:</span> {{ $event->venue }}</div>
            <div><span class="font-medium text-gray-700">Organizer:</span> {{ $event->organizer->name ?? '—' }}</div>
            <div><span class="font-medium text-gray-700">Start:</span> {{ $event->event_date->format('d M Y, h:i A') }}</div>
            <div><span class="font-medium text-gray-700">End:</span> {{ $event->end_date->format('d M Y, h:i A') }}</div>
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
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No RSVPs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Event Modal --}}
<x-modal name="edit-event" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Event</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.events.update', $event) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $event->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $event->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location / Venue</label>
                <input type="text" name="venue" value="{{ old('venue', $event->venue) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('venue') border-red-400 @enderror">
                @error('venue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="event_date"
                        value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('event_date') border-red-400 @enderror">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                    <input type="datetime-local" name="end_date"
                        value="{{ old('end_date', $event->end_date->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-400 @enderror">
                    @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Attendees <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="number" name="max_attendees" value="{{ old('max_attendees', $event->max_attendees) }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Unlimited">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['upcoming', 'ongoing', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ old('status', $event->status->value) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save Changes</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Delete Modal --}}
<x-modal name="delete-event" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Event</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $event->title }}</p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.events.destroy', $event) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
