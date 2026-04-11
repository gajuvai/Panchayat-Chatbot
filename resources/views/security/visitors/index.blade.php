@extends('layouts.app')
@section('title', 'Visitor Gate')

@section('content')
<div class="space-y-4">

    {{-- Date picker --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-800">Gate Dashboard</h1>
            <span class="text-sm text-gray-500">{{ $date->format('l, d M Y') }}</span>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Go</button>
            @if(!$date->isToday())
            <a href="{{ route('security.visitors.index') }}" class="text-indigo-600 text-sm hover:underline">Today</a>
            @endif
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Main pass list --}}
        <div class="lg:col-span-2 space-y-3">

            {{-- Checked In (active visitors) --}}
            @if(isset($passes['checked_in']) && $passes['checked_in']->isNotEmpty())
            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <h2 class="text-sm font-semibold text-green-800 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
                    Currently Inside ({{ $passes['checked_in']->count() }})
                </h2>
                <div class="space-y-2">
                    @foreach($passes['checked_in'] as $pass)
                    @include('security.visitors._pass-card', ['pass' => $pass, 'action' => 'check-out'])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Approved / Pending --}}
            @php
                $actionable = collect()
                    ->merge($passes['approved'] ?? [])
                    ->merge($passes['pending'] ?? [])
                    ->sortBy('expected_from');
            @endphp
            @if($actionable->isNotEmpty())
            <div class="bg-white border rounded-xl p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Expected Visitors ({{ $actionable->count() }})</h2>
                <div class="space-y-2">
                    @foreach($actionable as $pass)
                    @include('security.visitors._pass-card', ['pass' => $pass, 'action' => 'check-in'])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Checked Out --}}
            @if(isset($passes['checked_out']) && $passes['checked_out']->isNotEmpty())
            <div class="bg-gray-50 border rounded-xl p-4">
                <h2 class="text-sm font-semibold text-gray-500 mb-3">Checked Out ({{ $passes['checked_out']->count() }})</h2>
                <div class="space-y-2">
                    @foreach($passes['checked_out'] as $pass)
                    @include('security.visitors._pass-card', ['pass' => $pass, 'action' => null])
                    @endforeach
                </div>
            </div>
            @endif

            @if($passes->isEmpty())
            <div class="bg-white rounded-xl border p-12 text-center">
                <div class="text-5xl mb-3">🪪</div>
                <p class="text-gray-500 text-sm">No visitors expected on {{ $date->format('d M Y') }}.</p>
            </div>
            @endif
        </div>

        {{-- Sidebar: upcoming passes --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border p-4">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Next 7 Days</h2>
                @forelse($upcoming as $pass)
                <div class="py-2.5 border-b last:border-0 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-gray-800 truncate">{{ $pass->visitor_name }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded {{ $pass->status->badgeClass() }} flex-shrink-0">
                            {{ $pass->status->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $pass->expected_date->format('d M') }}
                        @if($pass->expected_from) · {{ $pass->expected_from }}@endif
                        · Flat {{ $pass->resident->block }}-{{ $pass->resident->flat_number }}
                    </p>
                    <p class="font-mono text-xs text-indigo-600">{{ $pass->pass_code }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No upcoming visitors.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
