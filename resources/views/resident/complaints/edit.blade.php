@extends('layouts.app')
@section('title', 'Edit Complaint')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('resident.complaints.show', $complaint) }}" class="text-indigo-600 text-sm hover:underline">← Back to Complaint</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Edit Complaint</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $complaint->complaint_number }}</p>
        </div>

        <form method="POST" action="{{ route('resident.complaints.update', $complaint) }}">
            @csrf
            @method('PATCH')

            <div class="space-y-5">
                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $complaint->title) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                        placeholder="Brief title of your complaint">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="5"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                        placeholder="Describe the issue in detail...">{{ old('description', $complaint->description) }}</textarea>
                    @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Priority & Location --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-red-500">*</span></label>
                        <select id="priority" name="priority"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('priority') border-red-400 @enderror">
                            @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                            <option value="{{ $p }}" {{ old('priority', $complaint->priority->value) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                        @error('priority')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location', $complaint->location) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="e.g. Block A, Floor 2">
                    </div>
                </div>

                {{-- Category (read-only, not editable after submission) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600">
                        {{ $complaint->category?->name ?? '—' }}
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Category cannot be changed after filing.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Update Complaint
                </button>
                <a href="{{ route('resident.complaints.show', $complaint) }}"
                    class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
