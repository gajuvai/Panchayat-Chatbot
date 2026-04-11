@extends('layouts.app')
@section('title', 'Complaint Categories')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $categories->count() }} category(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-category' }))"
            class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Description</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Complaints</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $category->description ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($category->is_active)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $category->complaints_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-category-{{ $category->id }}' }))"
                                class="text-indigo-600 hover:underline text-xs font-medium">Edit</button>

                            @if($category->complaints_count > 0)
                                <span class="text-xs text-gray-300 cursor-not-allowed" title="Cannot delete — has complaints attached">Delete</span>
                            @else
                                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-category-{{ $category->id }}' }))"
                                    class="text-xs text-red-500 hover:underline font-medium">Delete</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No categories found. Add one to get started.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Category Modal --}}
<x-modal name="create-category" :show="$errors->any() && !old('_edit_id')" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">New Complaint Category</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" autofocus
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                    placeholder="e.g. Road Damage">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                    placeholder="Brief description of what complaints belong in this category...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit"
                    class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Category
                </button>
                <button type="button" @click="show = false"
                    class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Edit & Delete Modals per category --}}
@foreach($categories as $category)

<x-modal name="edit-category-{{ $category->id }}" :show="old('_edit_id') == $category->id && $errors->any()" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Category</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="_edit_id" value="{{ $category->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('_edit_id') == $category->id ? old('name') : $category->name }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('_edit_id') == $category->id ? old('description') : $category->description }}</textarea>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active_{{ $category->id }}" value="1"
                    class="rounded border-gray-300 text-indigo-600"
                    {{ (old('_edit_id') == $category->id ? old('is_active') : $category->is_active) ? 'checked' : '' }}>
                <label for="is_active_{{ $category->id }}" class="text-sm text-gray-700">Active (visible to residents)</label>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit"
                    class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false"
                    class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

@if($category->complaints_count === 0)
<x-modal name="delete-category-{{ $category->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Category</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                Delete <span class="font-medium">{{ $category->name }}</span>?
            </p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
                    Delete
                </button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endif

@endforeach
@endsection
