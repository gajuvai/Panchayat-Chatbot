@extends('layouts.app')
@section('title', 'Security Incidents')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Security Incidents</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $incidents->total() }} incident(s) recorded</p>
        </div>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-incident' }))"
            class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Log Incident
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Severity</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Location</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Occurred At</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Reported By</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidents as $incident)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $incident->severityBadgeClass() }}">{{ ucfirst($incident->severity) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $incident->location }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusClass = match($incident->status) {
                                'active'        => 'bg-red-100 text-red-700',
                                'investigating' => 'bg-yellow-100 text-yellow-700',
                                'resolved'      => 'bg-green-100 text-green-700',
                                default         => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $statusClass }}">{{ ucfirst($incident->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $incident->occurred_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $incident->reporter->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('security.incidents.show', $incident) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-incident-{{ $incident->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-incident-{{ $incident->id }}' }))"
                                class="text-red-400 hover:underline text-xs">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No incidents recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $incidents->links() }}
</div>

{{-- Create Incident Modal --}}
<x-modal name="create-incident" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Log New Incident</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('security.incidents.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Incident Type <span class="text-red-500">*</span></label>
                <select name="incident_type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('incident_type') border-red-400 @enderror">
                    <option value="">— Select type —</option>
                    @foreach(['theft' => 'Theft', 'trespass' => 'Trespass', 'vandalism' => 'Vandalism',
                               'suspicious_activity' => 'Suspicious Activity', 'emergency' => 'Emergency', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ old('incident_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('incident_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity <span class="text-red-500">*</span></label>
                <select name="severity"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('severity') border-red-400 @enderror">
                    <option value="">— Select severity —</option>
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}" {{ old('severity') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('severity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" value="{{ old('location') }}"
                    placeholder="e.g. Gate 2, Block B corridor"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('location') border-red-400 @enderror">
                @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Occurred At <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="occurred_at"
                    value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('occurred_at') border-red-400 @enderror">
                @error('occurred_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4"
                    placeholder="Describe what happened in detail…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    Log Incident
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Edit & Delete Modals per incident --}}
@foreach($incidents as $incident)
@php $isActiveEdit = old('_edit_id') == $incident->id && $errors->any(); @endphp

<x-modal name="edit-incident-{{ $incident->id }}" :show="old('_edit_id') == $incident->id && $errors->any()" maxWidth="lg">
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
            <input type="hidden" name="_edit_id" value="{{ $incident->id }}">
            <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600 space-y-1 border border-gray-100">
                <p><span class="font-medium text-gray-700">Type:</span> {{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</p>
                <p><span class="font-medium text-gray-700">Location:</span> {{ $incident->location }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                    @foreach(['active' => 'Active', 'investigating' => 'Investigating', 'resolved' => 'Resolved'] as $val => $label)
                    <option value="{{ $val }}" {{ ($isActiveEdit ? old('status') : $incident->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select name="severity"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('severity') border-red-400 @enderror">
                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}" {{ ($isActiveEdit ? old('severity') : $incident->severity) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('severity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Update Incident
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

<x-modal name="delete-incident-{{ $incident->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Incident</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}</span>
                at {{ $incident->location }}
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

@endforeach
@endsection
