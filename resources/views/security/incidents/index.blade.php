@extends('layouts.app')
@section('title', 'Security Incidents')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Security Incidents</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $incidents->total() }} incident(s) recorded</p>
        </div>
        <a href="{{ route('security.incidents.create') }}"
           class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
            + Log Incident
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
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
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ ucfirst(str_replace('_', ' ', $incident->incident_type)) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $incident->severityBadgeClass() }}">
                            {{ ucfirst($incident->severity) }}
                        </span>
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
                        <span class="text-xs px-2 py-0.5 rounded {{ $statusClass }}">
                            {{ ucfirst($incident->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $incident->occurred_at->format('d M Y, h:i A') }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $incident->reporter->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('security.incidents.show', $incident) }}"
                               class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('security.incidents.edit', $incident) }}"
                               class="text-gray-500 hover:underline text-xs">Edit</a>
                            <form action="{{ route('security.incidents.destroy', $incident) }}" method="POST"
                                  onsubmit="return confirm('Delete this incident?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">No incidents recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $incidents->links() }}
</div>
@endsection
