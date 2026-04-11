@extends('layouts.app')
@section('title', 'Incident Details')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('security.incidents.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to Incidents</a>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-incident' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            Edit Incident
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border p-6 space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Reported by {{ $incident->reporter?->name ?? '—' }}
                    &middot; {{ $incident->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
            <span class="flex-shrink-0 text-xs px-3 py-1 rounded-full font-medium {{ $incident->severityBadgeClass() }}">
                {{ ucfirst($incident->severity) }}
            </span>
        </div>

        <hr class="border-gray-100">

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500 font-medium mb-0.5">Status</dt>
                <dd>
                    @php
                        $statusClass = match($incident->status) {
                            'active'        => 'bg-red-100 text-red-700',
                            'investigating' => 'bg-yellow-100 text-yellow-700',
                            'resolved'      => 'bg-green-100 text-green-700',
                            default         => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded {{ $statusClass }}">{{ ucfirst($incident->status) }}</span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 font-medium mb-0.5">Incident Type</dt>
                <dd class="text-gray-800">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 font-medium mb-0.5">Location</dt>
                <dd class="text-gray-800">{{ $incident->location }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 font-medium mb-0.5">Occurred At</dt>
                <dd class="text-gray-800">{{ $incident->occurred_at->format('d M Y, h:i A') }}</dd>
            </div>
            @if($incident->complaint_id)
            <div>
                <dt class="text-gray-500 font-medium mb-0.5">Linked Complaint</dt>
                <dd class="text-gray-800">#{{ $incident->complaint_id }}</dd>
            </div>
            @endif
        </dl>

        <div>
            <p class="text-gray-500 font-medium text-sm mb-1">Description</p>
            <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                {{ $incident->description }}
            </div>
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white rounded-xl border border-red-100 p-4 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-700">Delete Incident</p>
            <p class="text-xs text-gray-400">This action cannot be undone.</p>
        </div>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-incident' }))"
            class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-2 rounded-lg hover:bg-red-100 transition">
            Delete
        </button>
    </div>
</div>

{{-- Edit Incident Modal --}}
<x-modal name="edit-incident" :show="$errors->isNotEmpty()" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Update Incident</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }} &middot; {{ $incident->location }}</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('security.incidents.update', $incident) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                    @foreach(['active' => 'Active', 'investigating' => 'Investigating', 'resolved' => 'Resolved'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $incident->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('severity') border-red-400 @enderror">
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}" {{ old('severity', $incident->severity) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('severity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Update Incident</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Delete Modal --}}
<x-modal name="delete-incident" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Incident</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</span> at {{ $incident->location }}
            </p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('security.incidents.destroy', $incident) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
