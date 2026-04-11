@extends('layouts.app')
@section('title', 'Duty Roster')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-bold text-gray-800">Duty Roster Management</h1>
        <div class="flex items-center gap-2">
            <div class="flex bg-white rounded-lg border p-0.5 text-sm">
                <a href="{{ route('admin.duty-roster.index', ['tab' => 'upcoming']) }}"
                    class="px-3 py-1.5 rounded-md transition {{ $tab === 'upcoming' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:text-gray-700' }}">Upcoming</a>
                <a href="{{ route('admin.duty-roster.index', ['tab' => 'past']) }}"
                    class="px-3 py-1.5 rounded-md transition {{ $tab === 'past' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:text-gray-700' }}">Past</a>
            </div>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-roster' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Roster
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Roster Cards --}}
    @forelse($rosters as $roster)
    <div class="bg-white rounded-xl border overflow-hidden hover:shadow-sm transition">
        <div class="px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="text-lg">{{ $roster->type_icon }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ $roster->type_label }}</span>
                    @if($roster->is_open_signup)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">Open Signup</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $roster->roster_date->format('d M Y') }} · {{ $roster->shift_start }} – {{ $roster->shift_end }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $roster->title }}</h3>
                @if($roster->description)
                <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ $roster->description }}</p>
                @endif

                {{-- Assignments --}}
                @if($roster->assignments->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($roster->assignments as $a)
                    <div class="flex items-center gap-1 text-xs bg-gray-50 border rounded-full px-2 py-0.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $a->status === 'confirmed' ? 'bg-green-400' : ($a->status === 'declined' ? 'bg-red-400' : 'bg-blue-400') }}"></span>
                        {{ $a->user->name }}
                        @if($a->is_voluntary)<span class="text-green-500 text-xs">✓</span>@endif
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="text-xs text-gray-400 mt-1">
                    {{ $roster->filledSlots() }}/{{ $roster->slots_required }} slots filled
                </div>
            </div>

            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                @if($tab === 'upcoming')
                <div class="flex items-center gap-2">
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'assign-roster-{{ $roster->id }}' }))"
                        class="text-xs text-indigo-600 hover:underline font-medium">Assign</button>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-roster-{{ $roster->id }}' }))"
                        class="text-xs text-gray-500 hover:underline">Edit</button>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-roster-{{ $roster->id }}' }))"
                        class="text-xs text-red-400 hover:underline">Delete</button>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Assign Modal --}}
    @if($tab === 'upcoming')
    <x-modal name="assign-roster-{{ $roster->id }}" maxWidth="lg">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Assign Residents</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $roster->title }} · {{ $roster->roster_date->format('d M Y') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto max-h-[70vh]">
            <form action="{{ route('admin.duty-roster.assign', $roster) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Residents <span class="text-red-500">*</span></label>
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg divide-y">
                        @foreach($residents as $res)
                        @php $alreadyAssigned = $roster->isUserAssigned($res->id); @endphp
                        <label class="flex items-center gap-3 px-3 py-2.5 {{ $alreadyAssigned ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-50' }}">
                            <input type="checkbox" name="user_ids[]" value="{{ $res->id }}"
                                class="rounded border-gray-300 text-indigo-600"
                                {{ $alreadyAssigned ? 'disabled' : '' }}>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">{{ $res->name }}</p>
                                @if($res->flat_number)
                                <p class="text-xs text-gray-400">Flat {{ $res->block }}-{{ $res->flat_number }}</p>
                                @endif
                            </div>
                            @if($alreadyAssigned)
                            <span class="text-xs text-gray-400">Already assigned</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Instructions for the assigned residents..."></textarea>
                </div>
                <div class="flex gap-3 pt-2 border-t">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Assign</button>
                    <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                </div>
            </form>
            </div>
        </div>
    </x-modal>

    {{-- Edit Modal --}}
    <x-modal name="edit-roster-{{ $roster->id }}" :show="old('_edit_id') == $roster->id && $errors->any()" maxWidth="xl">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800">Edit Roster</h2>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @php $isActive = old('_edit_id') == $roster->id && $errors->any(); @endphp
            <form action="{{ route('admin.duty-roster.update', $roster) }}" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <input type="hidden" name="_edit_id" value="{{ $roster->id }}">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ $isActive ? old('title') : $roster->title }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['weekly_duty' => 'Weekly Duty','event_volunteer' => 'Event Volunteer','committee' => 'Committee','other' => 'Other'] as $v => $l)
                            <option value="{{ $v }}" {{ ($isActive ? old('type') : $roster->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="roster_date" value="{{ $isActive ? old('roster_date') : $roster->roster_date->format('Y-m-d') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start</label>
                        <input type="time" name="shift_start" value="{{ $isActive ? old('shift_start') : $roster->shift_start }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift End</label>
                        <input type="time" name="shift_end" value="{{ $isActive ? old('shift_end') : $roster->shift_end }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slots Required</label>
                        <input type="number" name="slots_required" value="{{ $isActive ? old('slots_required') : $roster->slots_required }}" min="1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_open_signup" value="0">
                            <input type="checkbox" name="is_open_signup" value="1"
                                class="rounded border-gray-300 text-indigo-600"
                                {{ ($isActive ? old('is_open_signup') : $roster->is_open_signup) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Open for self-signup</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $isActive ? old('description') : $roster->description }}</textarea>
                </div>
                <div class="flex gap-3 pt-2 border-t">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save</button>
                    <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Delete Modal --}}
    <x-modal name="delete-roster-{{ $roster->id }}" maxWidth="sm">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Delete Roster</h3>
                        <p class="text-sm text-gray-500 mt-0.5">All assignments will also be removed.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $roster->title }}</p>
            </div>
            <div class="flex items-center gap-3 px-6 pb-5">
                <form action="{{ route('admin.duty-roster.destroy', $roster) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
                </form>
                <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
            </div>
        </div>
    </x-modal>
    @endif

    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">📋</div>
        <p class="text-gray-500 text-sm">No {{ $tab }} rosters.</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-roster' }))"
            class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Create the first roster</button>
    </div>
    @endforelse

    {{ $rosters->links() }}
</div>

{{-- Create Roster Modal --}}
<x-modal name="create-roster" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Create New Roster</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.duty-roster.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Gate Duty — Week 15, Diwali Event Volunteers">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['weekly_duty' => 'Weekly Duty','event_volunteer' => 'Event Volunteer','committee' => 'Committee','other' => 'Other'] as $v => $l)
                        <option value="{{ $v }}" {{ old('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="roster_date" value="{{ old('roster_date') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('roster_date') border-red-400 @enderror">
                    @error('roster_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start <span class="text-red-500">*</span></label>
                    <input type="time" name="shift_start" value="{{ old('shift_start', '08:00') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_start') border-red-400 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift End <span class="text-red-500">*</span></label>
                    <input type="time" name="shift_end" value="{{ old('shift_end', '12:00') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('shift_end') border-red-400 @enderror">
                    @error('shift_end')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slots Required</label>
                    <input type="number" name="slots_required" value="{{ old('slots_required', 1) }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_open_signup" value="0">
                        <input type="checkbox" name="is_open_signup" value="1"
                            class="rounded border-gray-300 text-indigo-600"
                            {{ old('is_open_signup') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Allow self-signup</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Details about this duty shift...">{{ old('description') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Create Roster</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endsection
