@extends('layouts.app')
@section('title', 'Patrol Assignment Details')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
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
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-patrol' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Edit</button>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-patrol' }))"
                class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition">Delete</button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
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
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Assigned By</dt>
                <dd class="col-span-2 text-sm text-gray-800">{{ $patrol->assignedBy?->name ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Shift Start</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    {{ $patrol->shift_start->format('d M Y, h:i A') }}
                    <span class="text-gray-400 text-xs ml-2">({{ $patrol->shift_start->diffForHumans() }})</span>
                </dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Shift End</dt>
                <dd class="col-span-2 text-sm text-gray-800">
                    {{ $patrol->shift_end->format('d M Y, h:i A') }}
                    @php
                        $duration = $patrol->shift_start->diffInMinutes($patrol->shift_end);
                        $hours = intdiv($duration, 60); $mins = $duration % 60;
                    @endphp
                    <span class="text-gray-400 text-xs ml-2">({{ $hours > 0 ? "{$hours}h " : '' }}{{ $mins > 0 ? "{$mins}m" : '' }} duration)</span>
                </dd>
            </div>
            @if($patrol->notes)
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="col-span-2 text-sm text-gray-700 whitespace-pre-line">{{ $patrol->notes }}</dd>
            </div>
            @endif
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="col-span-2 text-sm text-gray-500">{{ $patrol->created_at->format('d M Y, h:i A') }}</dd>
            </div>
        </dl>
    </div>
</div>

{{-- Edit Patrol Modal --}}
<x-modal name="edit-patrol" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Patrol Assignment</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('security.patrols.update', $patrol) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area / Zone <span class="text-red-500">*</span></label>
                <input type="text" name="area" value="{{ old('area', $patrol->area) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('area') border-red-400 @enderror">
                @error('area')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign Officer <span class="text-red-500">*</span></label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('assigned_to') border-red-400 @enderror">
                    <option value="">— Select Officer —</option>
                    @foreach($officers as $officer)
                    <option value="{{ $officer->id }}" {{ old('assigned_to', $patrol->assigned_to) == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_start"
                        value="{{ old('shift_start', $patrol->shift_start->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_start') border-red-400 @enderror">
                    @error('shift_start')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift End <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_end"
                        value="{{ old('shift_end', $patrol->shift_end->format('Y-m-d\TH:i')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_end') border-red-400 @enderror">
                    @error('shift_end')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $patrol->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Optional instructions or remarks...">{{ old('notes', $patrol->notes) }}</textarea>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save Changes</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Delete Modal --}}
<x-modal name="delete-patrol" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Assignment</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                <span class="font-medium">{{ $patrol->area }}</span> &middot; {{ $patrol->assignedTo?->name ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('security.patrols.destroy', $patrol) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
