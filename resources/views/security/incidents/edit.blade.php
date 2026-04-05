@extends('layouts.app')
@section('title', 'Edit Incident')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('security.incidents.show', $incident) }}"
       class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Incident</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-1">Edit Incident</h1>
        <p class="text-sm text-gray-500 mb-4">
            {{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}
            &middot; {{ $incident->location }}
            &middot; {{ $incident->occurred_at->format('d M Y, h:i A') }}
        </p>

        <form action="{{ route('security.incidents.update', $incident) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            {{-- Read-only context fields (not submitted, for reference) --}}
            <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600 space-y-1 border border-gray-100">
                <p><span class="font-medium text-gray-700">Type:</span>
                   {{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</p>
                <p><span class="font-medium text-gray-700">Location:</span> {{ $incident->location }}</p>
                <p><span class="font-medium text-gray-700">Description:</span> {{ $incident->description }}</p>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('status') border-red-400 @enderror">
                    @foreach(['active' => 'Active', 'investigating' => 'Investigating', 'resolved' => 'Resolved'] as $val => $label)
                    <option value="{{ $val }}"
                        {{ old('status', $incident->status) === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Severity --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('severity') border-red-400 @enderror">
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}"
                        {{ old('severity', $incident->severity) === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
                @error('severity')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Update Incident
                </button>
                <a href="{{ route('security.incidents.show', $incident) }}"
                   class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
