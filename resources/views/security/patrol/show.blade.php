@extends('layouts.app')
@section('title', 'Patrol Assignment Details')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('security.patrols.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-800">Patrol Assignment</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('security.patrols.edit', $patrol) }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Edit
            </a>
            <form action="{{ route('security.patrols.destroy', $patrol) }}" method="POST"
                  onsubmit="return confirm('Delete this patrol assignment?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Details card --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        {{-- Status banner --}}
        @php
            $statusClasses = [
                'scheduled'   => 'bg-blue-50 border-blue-200 text-blue-700',
                'in_progress' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
                'completed'   => 'bg-green-50 border-green-200 text-green-700',
                'cancelled'   => 'bg-gray-50 border-gray-200 text-gray-500',
            ];
            $cls = $statusClasses[$patrol->status] ?? 'bg-gray-50 border-gray-200 text-gray-500';
        @endphp
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 text-base">{{ $patrol->area }}</h2>
            <span class="text-xs px-3 py-1 rounded-full border font-medium {{ $cls }}">
                {{ ucfirst(str_replace('_', ' ', $patrol->status)) }}
            </span>
        </div>

        <dl class="divide-y divide-gray-100">
            {{-- Assigned Officer --}}
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Assigned Officer</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    @if($patrol->assignedTo)
                        <span class="font-medium">{{ $patrol->assignedTo->name }}</span>
                        @if($patrol->assignedTo->phone)
                            <span class="text-gray-400 ml-2 text-xs">{{ $patrol->assignedTo->phone }}</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>

            {{-- Assigned By --}}
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Assigned By</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    {{ $patrol->assignedBy?->name ?? '—' }}
                </dd>
            </div>

            {{-- Shift Start --}}
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Shift Start</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    {{ $patrol->shift_start->format('d M Y, h:i A') }}
                    <span class="text-gray-400 text-xs ml-2">({{ $patrol->shift_start->diffForHumans() }})</span>
                </dd>
            </div>

            {{-- Shift End --}}
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Shift End</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    {{ $patrol->shift_end->format('d M Y, h:i A') }}
                    @php
                        $duration = $patrol->shift_start->diffInMinutes($patrol->shift_end);
                        $hours    = intdiv($duration, 60);
                        $mins     = $duration % 60;
                    @endphp
                    <span class="text-gray-400 text-xs ml-2">
                        ({{ $hours > 0 ? "{$hours}h " : '' }}{{ $mins > 0 ? "{$mins}m" : '' }} duration)
                    </span>
                </dd>
            </div>

            {{-- Notes --}}
            @if($patrol->notes)
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="col-span-2 text-sm text-gray-700 whitespace-pre-line">{{ $patrol->notes }}</dd>
            </div>
            @endif

            {{-- Created At --}}
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="col-span-2 text-sm text-gray-500">{{ $patrol->created_at->format('d M Y, h:i A') }}</dd>
            </div>
        </dl>
    </div>

</div>
@endsection
