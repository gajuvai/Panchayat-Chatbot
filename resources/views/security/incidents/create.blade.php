@extends('layouts.app')
@section('title', 'Log Incident')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('security.incidents.index') }}"
       class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Incidents</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Log New Incident</h1>

        <form action="{{ route('security.incidents.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Incident Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Incident Type</label>
                <select name="incident_type"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('incident_type') border-red-400 @enderror">
                    <option value="">— Select type —</option>
                    @foreach(['theft' => 'Theft', 'trespass' => 'Trespass', 'vandalism' => 'Vandalism',
                               'suspicious_activity' => 'Suspicious Activity', 'emergency' => 'Emergency', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ old('incident_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('incident_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Severity --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('severity') border-red-400 @enderror">
                    <option value="">— Select severity —</option>
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}" {{ old('severity') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('severity')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Location --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       placeholder="e.g. Gate 2, Block B corridor"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('location') border-red-400 @enderror">
                @error('location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Occurred At --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Occurred At</label>
                <input type="datetime-local" name="occurred_at"
                       value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('occurred_at') border-red-400 @enderror">
                @error('occurred_at')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="5"
                          placeholder="Describe what happened in detail…"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    Log Incident
                </button>
                <a href="{{ route('security.incidents.index') }}"
                   class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
