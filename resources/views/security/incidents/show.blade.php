@extends('layouts.app')
@section('title', 'Incident Details')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('security.incidents.index') }}"
           class="text-indigo-600 text-sm hover:underline">← Back to Incidents</a>
        <a href="{{ route('security.incidents.edit', $incident) }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            Edit Incident
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl border p-6 space-y-5">
        {{-- Header row: type + severity badge --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    {{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}
                </h1>
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

        {{-- Detail grid --}}
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
                    <span class="text-xs px-2 py-0.5 rounded {{ $statusClass }}">
                        {{ ucfirst($incident->status) }}
                    </span>
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
        <form action="{{ route('security.incidents.destroy', $incident) }}" method="POST"
              onsubmit="return confirm('Permanently delete this incident?')">
            @csrf @method('DELETE')
            <button class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-2 rounded-lg hover:bg-red-100 transition">
                Delete
            </button>
        </form>
    </div>
</div>
@endsection
