@extends('layouts.app')
@section('title', 'Create Event')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.events.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Events</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Create New Event</h1>

        <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror"
                    placeholder="Event title">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('description') border-red-400 @enderror"
                    placeholder="Describe the event...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location / Venue</label>
                <input type="text" name="venue" value="{{ old('venue') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('venue') border-red-400 @enderror"
                    placeholder="e.g. Community Hall, Block A">
                @error('venue')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                    <input type="datetime-local" name="event_date" value="{{ old('event_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('event_date') border-red-400 @enderror">
                    @error('event_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('end_date') border-red-400 @enderror">
                    @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Max Attendees <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input type="number" name="max_attendees" value="{{ old('max_attendees') }}" min="1"
                    class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm @error('max_attendees') border-red-400 @enderror"
                    placeholder="Unlimited">
                @error('max_attendees')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Create Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
