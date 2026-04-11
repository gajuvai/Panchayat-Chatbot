@extends('layouts.app')
@section('title', 'Visitor Passes')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $passes->total() }} pass(es) found</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-pass' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Register Visitor
        </button>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="date" name="date" value="{{ request('date') }}"
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(\App\Enums\VisitorPassStatus::cases() as $s)
            <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200">Filter</button>
        <a href="{{ route('resident.visitor-passes.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    {{-- Pass Cards --}}
    @forelse($passes as $pass)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                        {{ $pass->pass_code }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $pass->status->badgeClass() }}">
                        {{ $pass->status->label() }}
                    </span>
                </div>
                <h3 class="font-semibold text-gray-800 text-base">{{ $pass->visitor_name }}</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-gray-500">
                    <span>📅 {{ $pass->expected_date->format('d M Y') }}</span>
                    @if($pass->expected_from)
                    <span>🕐 {{ $pass->expected_from }} – {{ $pass->expected_to ?? '—' }}</span>
                    @endif
                    @if($pass->visitor_phone)
                    <span>📞 {{ $pass->visitor_phone }}</span>
                    @endif
                    @if($pass->vehicle_number)
                    <span>🚗 {{ $pass->vehicle_number }}</span>
                    @endif
                    @if($pass->purpose)
                    <span>📝 {{ $pass->purpose }}</span>
                    @endif
                </div>
                @if($pass->checked_in_at)
                <div class="mt-1 text-xs text-gray-400">
                    Checked in: {{ $pass->checked_in_at->format('d M Y, h:i A') }}
                    @if($pass->checked_out_at)
                    · Checked out: {{ $pass->checked_out_at->format('h:i A') }}
                    @endif
                </div>
                @endif
            </div>
            @if($pass->isCancellable())
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'cancel-pass-{{ $pass->id }}' }))"
                class="flex-shrink-0 text-xs text-red-500 hover:underline font-medium">
                Cancel
            </button>
            @endif
        </div>
    </div>

    {{-- Cancel Modal --}}
    @if($pass->isCancellable())
    <x-modal name="cancel-pass-{{ $pass->id }}" maxWidth="sm">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Cancel Visitor Pass</h3>
                        <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                    <span class="font-mono font-bold text-indigo-600">{{ $pass->pass_code }}</span>
                    — {{ $pass->visitor_name }} on {{ $pass->expected_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3 px-6 pb-5">
                <form action="{{ route('resident.visitor-passes.cancel', $pass) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                        Cancel Pass
                    </button>
                </form>
                <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Keep</button>
            </div>
        </div>
    </x-modal>
    @endif

    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">🪪</div>
        <p class="text-gray-500 text-sm">No visitor passes yet.</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-pass' }))"
            class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Register your first visitor</button>
    </div>
    @endforelse

    {{ $passes->links() }}
</div>

{{-- Create Pass Modal --}}
<x-modal name="create-pass" :show="$errors->any()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Register a Visitor</h2>
                <p class="text-xs text-gray-500 mt-0.5">A pass code will be generated and security will be notified.</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('resident.visitor-passes.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visitor Name <span class="text-red-500">*</span></label>
                    <input type="text" name="visitor_name" value="{{ old('visitor_name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('visitor_name') border-red-400 @enderror"
                        placeholder="Full name">
                    @error('visitor_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visitor Phone</label>
                    <input type="text" name="visitor_phone" value="{{ old('visitor_phone') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="+977-98XXXXXXXX">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. BA 1 PA 2345">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Family visit, Delivery">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Date <span class="text-red-500">*</span></label>
                <input type="date" name="expected_date" value="{{ old('expected_date', today()->format('Y-m-d')) }}"
                    min="{{ today()->format('Y-m-d') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expected_date') border-red-400 @enderror">
                @error('expected_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected From</label>
                    <input type="time" name="expected_from" value="{{ old('expected_from') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expected Until</label>
                    <input type="time" name="expected_to" value="{{ old('expected_to') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('expected_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Any special instructions for security...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Register Visitor
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endsection
