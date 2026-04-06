@extends('layouts.app')
@section('title', 'Emergency Alert Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('security.alerts.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Alerts</a>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
        {{ session('error') }}
    </div>
    @endif

    {{-- Alert card --}}
    <div class="bg-white rounded-xl border-2 {{ $alert->is_active ? 'border-red-300' : 'border-gray-200' }} overflow-hidden">

        {{-- Coloured header strip --}}
        <div class="{{ $alert->is_active ? 'bg-red-600' : 'bg-gray-500' }} px-6 py-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-white {{ $alert->is_active ? 'animate-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <span class="text-xs text-white/70 uppercase tracking-wide font-medium">
                            {{ $alert->is_active ? 'Active Emergency' : 'Resolved Alert' }}
                        </span>
                        <p class="text-white font-bold text-lg">
                            {{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}
                        </p>
                    </div>
                </div>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold
                    {{ $alert->is_active ? 'bg-white text-red-600' : 'bg-white/20 text-white' }}">
                    {{ $alert->is_active ? 'ACTIVE' : 'RESOLVED' }}
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Alert type badge --}}
            <div>
                <span class="inline-block text-xs px-3 py-1 rounded-full font-medium {{ $alert->alertTypeBadgeClass() }}">
                    {{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}
                </span>
            </div>

            {{-- Message --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Message</h2>
                <p class="text-gray-800 text-sm leading-relaxed">{{ $alert->message }}</p>
            </div>

            {{-- Meta info grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Triggered By</p>
                    <p class="text-sm font-medium text-gray-800">{{ $alert->triggeredBy?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Triggered At</p>
                    <p class="text-sm font-medium text-gray-800">{{ $alert->created_at->format('d M Y, h:i A') }}</p>
                    <p class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</p>
                </div>

                @if(! $alert->is_active)
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Resolved By</p>
                    <p class="text-sm font-medium text-gray-800">{{ $alert->resolvedBy?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Resolved At</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $alert->resolved_at?->format('d M Y, h:i A') ?? '—' }}
                    </p>
                    @if($alert->resolved_at)
                    <p class="text-xs text-gray-400">{{ $alert->resolved_at->diffForHumans() }}</p>
                    @endif
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-4 border-t border-gray-100">
                @if($alert->is_active)
                <form action="{{ route('security.alerts.resolve', $alert) }}" method="POST"
                    onsubmit="return confirm('Mark this emergency alert as resolved?')">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                        Mark as Resolved
                    </button>
                </form>
                @else
                <span class="inline-flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 px-4 py-2 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    This alert has been resolved
                </span>
                @endif

                <a href="{{ route('security.alerts.index') }}"
                    class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
