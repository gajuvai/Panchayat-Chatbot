@extends('layouts.app')
@section('title', 'Patrol Assignments')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $patrols->total() }} patrol assignment(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-patrol' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Assignment
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
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
                        <span class="text-xs px-2 py-0.5 rounded {{ $cls }}">{{ ucfirst(str_replace('_', ' ', $patrol->status)) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('security.patrols.show', $patrol) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-patrol-{{ $patrol->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-patrol-{{ $patrol->id }}' }))"
                                class="text-red-400 hover:underline text-xs">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No patrol assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $patrols->links() }}
</div>

{{-- Create Patrol Modal --}}
<x-modal name="create-patrol" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">New Patrol Assignment</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('security.patrols.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area / Zone <span class="text-red-500">*</span></label>
                <input type="text" name="area" value="{{ old('area') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('area') border-red-400 @enderror"
                    placeholder="e.g. Block A Gate, North Perimeter">
                @error('area')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign Officer <span class="text-red-500">*</span></label>
                <select name="assigned_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('assigned_to') border-red-400 @enderror">
                    <option value="">— Select Officer —</option>
                    @foreach($officers as $officer)
                    <option value="{{ $officer->id }}" {{ old('assigned_to') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                    @endforeach
                </select>
                @error('assigned_to')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_start" value="{{ old('shift_start') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_start') border-red-400 @enderror">
                    @error('shift_start')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift End <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_end" value="{{ old('shift_end') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_end') border-red-400 @enderror">
                    @error('shift_end')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                    <option value="scheduled" {{ old('status', 'scheduled') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Optional instructions or remarks...">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Assignment
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Edit & Delete Modals per patrol --}}
@foreach($patrols as $patrol)
@php $isActiveEdit = old('_edit_id') == $patrol->id && $errors->any(); @endphp

<x-modal name="edit-patrol-{{ $patrol->id }}" :show="old('_edit_id') == $patrol->id && $errors->any()" maxWidth="xl">
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
            <input type="hidden" name="_edit_id" value="{{ $patrol->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area / Zone <span class="text-red-500">*</span></label>
                <input type="text" name="area" value="{{ $isActiveEdit ? old('area') : $patrol->area }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('area') border-red-400 @enderror"
                    placeholder="e.g. Block A Gate, North Perimeter">
                @error('area')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign Officer <span class="text-red-500">*</span></label>
                <select name="assigned_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('assigned_to') border-red-400 @enderror">
                    <option value="">— Select Officer —</option>
                    @foreach($officers as $officer)
                    <option value="{{ $officer->id }}"
                        {{ ($isActiveEdit ? old('assigned_to') : $patrol->assigned_to) == $officer->id ? 'selected' : '' }}>
                        {{ $officer->name }}
                    </option>
                    @endforeach
                </select>
                @error('assigned_to')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_start"
                        value="{{ $isActiveEdit ? old('shift_start') : $patrol->shift_start->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_start') border-red-400 @enderror">
                    @error('shift_start')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift End <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="shift_end"
                        value="{{ $isActiveEdit ? old('shift_end') : $patrol->shift_end->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_end') border-red-400 @enderror">
                    @error('shift_end')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                    @foreach(['scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" {{ ($isActiveEdit ? old('status') : $patrol->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Optional instructions or remarks...">{{ $isActiveEdit ? old('notes') : $patrol->notes }}</textarea>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

<x-modal name="delete-patrol-{{ $patrol->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Assignment</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                <span class="font-medium">{{ $patrol->area }}</span>
                &middot; {{ $patrol->assignedTo?->name ?? '—' }}
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

@endforeach
@endsection
