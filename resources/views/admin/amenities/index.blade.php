@extends('layouts.app')
@section('title', 'Amenity Management')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Amenity Management</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $amenities->count() }} amenity(s)</p>
        </div>
        <div class="flex items-center gap-2">
            @if($pendingTotal > 0)
            <a href="{{ route('admin.amenity-bookings.index', ['status' => 'pending']) }}"
                class="bg-yellow-100 text-yellow-700 border border-yellow-300 px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-200 transition">
                {{ $pendingTotal }} Pending Approvals
            </a>
            @endif
            <a href="{{ route('admin.amenity-bookings.index') }}"
                class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">All Bookings</a>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-amenity' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Amenity
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($amenities as $amenity)
        <div class="bg-white rounded-xl border overflow-hidden hover:shadow-sm transition">
            @if($amenity->photo_url)
            <div class="h-32 w-full overflow-hidden bg-gray-100">
                <img src="{{ $amenity->photo_url }}" class="w-full h-full object-cover" alt="{{ $amenity->name }}">
            </div>
            @else
            <div class="h-32 bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center">
                <span class="text-4xl">{{ $amenity->type_icon }}</span>
            </div>
            @endif

            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-semibold text-gray-800">{{ $amenity->name }}</h3>
                    @if(!$amenity->is_active)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inactive</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mb-2">{{ ucfirst($amenity->type) }} · Capacity {{ $amenity->capacity }}</p>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $amenity->is_free ? 'Free' : 'Rs. ' . number_format($amenity->fee_per_hour, 0) . '/hr' }}</span>
                    @if($amenity->pending_count > 0)
                    <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">{{ $amenity->pending_count }} pending</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-3 pt-3 border-t">
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-amenity-{{ $amenity->id }}' }))"
                        class="text-xs text-indigo-600 hover:underline font-medium">Edit</button>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-amenity-{{ $amenity->id }}' }))"
                        class="text-xs text-red-400 hover:underline ml-auto">Delete</button>
                </div>
            </div>
        </div>

        {{-- Edit Modal --}}
        <x-modal name="edit-amenity-{{ $amenity->id }}" :show="old('_edit_id') == $amenity->id && $errors->any()" maxWidth="xl">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-base font-semibold text-gray-800">Edit Amenity</h2>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @php $isActive = old('_edit_id') == $amenity->id && $errors->any(); @endphp
                <div class="overflow-y-auto max-h-[80vh]">
                <form action="{{ route('admin.amenities.update', $amenity) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="_edit_id" value="{{ $amenity->id }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ $isActive ? old('name') : $amenity->name }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach(['parking','hall','gym','pool','garden','other'] as $t)
                                <option value="{{ $t }}" {{ ($isActive ? old('type') : $amenity->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $isActive ? old('description') : $amenity->description }}</textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input type="number" name="capacity" value="{{ $isActive ? old('capacity') : $amenity->capacity }}" min="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fee/Hour (Rs.)</label>
                            <input type="number" name="fee_per_hour" value="{{ $isActive ? old('fee_per_hour') : $amenity->fee_per_hour }}" min="0" step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex flex-col gap-2 justify-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="requires_approval" value="0">
                                <input type="checkbox" name="requires_approval" value="1"
                                    class="rounded border-gray-300 text-indigo-600"
                                    {{ ($isActive ? old('requires_approval') : $amenity->requires_approval) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">Requires Approval</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1"
                                    class="rounded border-gray-300 text-indigo-600"
                                    {{ ($isActive ? old('is_active') : $amenity->is_active) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Opening Time</label>
                            <input type="time" name="opening_time" value="{{ $isActive ? old('opening_time') : $amenity->opening_time }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Closing Time</label>
                            <input type="time" name="closing_time" value="{{ $isActive ? old('closing_time') : $amenity->closing_time }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Replace Photo</label>
                        <input type="file" name="photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
        <x-modal name="delete-amenity-{{ $amenity->id }}" maxWidth="sm">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Delete Amenity</h3>
                            <p class="text-sm text-gray-500 mt-0.5">All bookings will also be removed.</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $amenity->name }}</p>
                </div>
                <div class="flex items-center gap-3 px-6 pb-5">
                    <form action="{{ route('admin.amenities.destroy', $amenity) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
                    </form>
                    <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                </div>
            </div>
        </x-modal>

        @empty
        <div class="col-span-3 bg-white rounded-xl border p-12 text-center">
            <div class="text-5xl mb-3">🏢</div>
            <p class="text-gray-500 text-sm">No amenities yet. Add your first one!</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Create Amenity Modal --}}
<x-modal name="create-amenity" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Add New Amenity</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.amenities.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                        placeholder="e.g. Community Hall">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['parking','hall','gym','pool','garden','other'] as $t)
                        <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Brief description for residents...">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" value="{{ old('capacity', 1) }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fee/Hour (Rs.)</label>
                    <input type="number" name="fee_per_hour" value="{{ old('fee_per_hour', 0) }}" min="0" step="0.01"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">0 = free</p>
                </div>
                <div class="flex flex-col gap-2 justify-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="requires_approval" value="0">
                        <input type="checkbox" name="requires_approval" value="1"
                            class="rounded border-gray-300 text-indigo-600"
                            {{ old('requires_approval') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Requires Approval</span>
                    </label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Time</label>
                    <input type="time" name="opening_time" value="{{ old('opening_time') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Closing Time</label>
                    <input type="time" name="closing_time" value="{{ old('closing_time') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="file" name="photo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Create Amenity</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endsection
