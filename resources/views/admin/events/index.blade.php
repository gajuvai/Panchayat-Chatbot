@extends('layouts.app')
@section('title', 'Events')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $events->total() }} event(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-event' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Event
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

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
                        <span class="text-xs px-2 py-0.5 rounded {{ $event->status->badgeClass() }}">{{ $event->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-event-{{ $event->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-event-{{ $event->id }}' }))"
                                class="text-red-400 hover:underline text-xs">Delete</button>
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

{{-- Create Event Modal --}}
<x-modal name="create-event" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Create New Event</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.events.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="Event title">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                    placeholder="Describe the event...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location / Venue <span class="text-red-500">*</span></label>
                <input type="text" name="venue" value="{{ old('venue') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('venue') border-red-400 @enderror"
                    placeholder="e.g. Community Hall, Block A">
                @error('venue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="event_date" value="{{ old('event_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('event_date') border-red-400 @enderror">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-400 @enderror">
                    @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Attendees <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="number" name="max_attendees" value="{{ old('max_attendees') }}" min="1"
                    class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Unlimited">
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Event
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Edit & Delete Modals per event --}}
@foreach($events as $event)
@php $isActiveEdit = old('_edit_id') == $event->id && $errors->any(); @endphp

<x-modal name="edit-event-{{ $event->id }}" :show="old('_edit_id') == $event->id && $errors->any()" maxWidth="xl">
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
            <input type="hidden" name="_edit_id" value="{{ $event->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ $isActiveEdit ? old('title') : $event->title }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $isActiveEdit ? old('description') : $event->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location / Venue</label>
                <input type="text" name="venue" value="{{ $isActiveEdit ? old('venue') : $event->venue }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('venue') border-red-400 @enderror">
                @error('venue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="event_date"
                        value="{{ $isActiveEdit ? old('event_date') : $event->event_date->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('event_date') border-red-400 @enderror">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                    <input type="datetime-local" name="end_date"
                        value="{{ $isActiveEdit ? old('end_date') : $event->end_date->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_date') border-red-400 @enderror">
                    @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Attendees <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="number" name="max_attendees"
                        value="{{ $isActiveEdit ? old('max_attendees') : $event->max_attendees }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Unlimited">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['upcoming', 'ongoing', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($isActiveEdit ? old('status') : $event->status->value) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

<x-modal name="delete-event-{{ $event->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Event</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
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

@endforeach
@endsection
