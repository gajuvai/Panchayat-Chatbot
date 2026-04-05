@extends('layouts.app')
@section('title', 'Patrol Assignments')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $patrols->total() }} patrol assignment(s)</p>
        <a href="{{ route('security.patrols.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + New Assignment
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
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Area / Zone</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Officer</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Shift Start</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Shift End</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($patrols as $patrol)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $patrol->area }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $patrol->assignedTo?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $patrol->shift_start->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $patrol->shift_end->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusClasses = [
                                'scheduled'   => 'bg-blue-100 text-blue-700',
                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                'completed'   => 'bg-green-100 text-green-700',
                                'cancelled'   => 'bg-gray-100 text-gray-500',
                            ];
                            $cls = $statusClasses[$patrol->status] ?? 'bg-gray-100 text-gray-500';
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded {{ $cls }}">
                            {{ ucfirst(str_replace('_', ' ', $patrol->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('security.patrols.show', $patrol) }}"
                               class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('security.patrols.edit', $patrol) }}"
                               class="text-gray-500 hover:underline text-xs">Edit</a>
                            <form action="{{ route('security.patrols.destroy', $patrol) }}" method="POST"
                                  onsubmit="return confirm('Delete this patrol assignment?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No patrol assignments yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $patrols->links() }}
</div>
@endsection
