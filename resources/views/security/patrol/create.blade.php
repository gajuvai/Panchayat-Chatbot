@extends('layouts.app')
@section('title', 'New Patrol Assignment')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('security.patrols.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold text-gray-800">New Patrol Assignment</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('security.patrols.store') }}" method="POST"
          class="bg-white rounded-xl border p-6 space-y-5">
        @csrf

        {{-- Area / Zone --}}
        <div>
            <label for="area" class="block text-sm font-medium text-gray-700 mb-1">Area / Zone <span class="text-red-500">*</span></label>
            <input type="text" id="area" name="area" value="{{ old('area') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('area') border-red-400 @enderror"
                   placeholder="e.g. Block A Gate, North Perimeter">
            @error('area')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Assigned Officer --}}
        <div>
            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assign Officer <span class="text-red-500">*</span></label>
            <select id="assigned_to" name="assigned_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('assigned_to') border-red-400 @enderror">
                <option value="">— Select Officer —</option>
                @foreach($officers as $officer)
                <option value="{{ $officer->id }}" {{ old('assigned_to') == $officer->id ? 'selected' : '' }}>
                    {{ $officer->name }}
                </option>
                @endforeach
            </select>
            @error('assigned_to')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Shift Start & End --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="shift_start" class="block text-sm font-medium text-gray-700 mb-1">Shift Start <span class="text-red-500">*</span></label>
                <input type="datetime-local" id="shift_start" name="shift_start" value="{{ old('shift_start') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_start') border-red-400 @enderror">
                @error('shift_start')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="shift_end" class="block text-sm font-medium text-gray-700 mb-1">Shift End <span class="text-red-500">*</span></label>
                <input type="datetime-local" id="shift_end" name="shift_end" value="{{ old('shift_end') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_end') border-red-400 @enderror">
                @error('shift_end')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                <option value="scheduled"   {{ old('status', 'scheduled') === 'scheduled'   ? 'selected' : '' }}>Scheduled</option>
                <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed"   {{ old('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                <option value="cancelled"   {{ old('status') === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-400 @enderror"
                      placeholder="Optional instructions or remarks...">{{ old('notes') }}</textarea>
            @error('notes')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Create Assignment
            </button>
            <a href="{{ route('security.patrols.index') }}"
               class="text-gray-500 text-sm hover:underline">Cancel</a>
        </div>
    </form>
</div>
@endsection
