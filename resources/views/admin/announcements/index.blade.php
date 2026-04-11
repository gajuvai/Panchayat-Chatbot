@extends('layouts.app')
@section('title', 'Announcements')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $announcements->total() }} announcement(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-announcement' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Target</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $ann)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $ann->title }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $ann->typeBadgeClass() }}">{{ ucfirst($ann->type) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $ann->target_role ? ucfirst(str_replace('_', ' ', $ann->target_role)) : 'All' }}</td>
                    <td class="px-4 py-3">
                        @if($ann->is_published)
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $ann->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.announcements.show', $ann) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-ann-{{ $ann->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit</button>
                            <form action="{{ route('admin.announcements.publish', $ann) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs {{ $ann->is_published ? 'text-yellow-600' : 'text-green-600' }} hover:underline">
                                    {{ $ann->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-ann-{{ $ann->id }}' }))"
                                class="text-xs text-red-500 hover:underline">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $announcements->links() }}
</div>

{{-- Create Announcement Modal --}}
<x-modal name="create-announcement" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">New Announcement</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.announcements.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['general','urgent','maintenance','event'] as $t)
                        <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Role</label>
                    <select name="target_role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        <option value="resident" {{ old('target_role') === 'resident' ? 'selected' : '' }}>Resident</option>
                        <option value="admin" {{ old('target_role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="security_head" {{ old('target_role') === 'security_head' ? 'selected' : '' }}>Security Head</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Announcement
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

{{-- Edit & Delete Modals per announcement --}}
@foreach($announcements as $ann)

<x-modal name="edit-ann-{{ $ann->id }}" :show="old('_edit_id') == $ann->id && $errors->any()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Announcement</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.announcements.update', $ann) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <input type="hidden" name="_edit_id" value="{{ $ann->id }}">
            @php $isActive = old('_edit_id') == $ann->id && $errors->any(); @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ $isActive ? old('title') : $ann->title }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror">{{ $isActive ? old('body') : $ann->body }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['general','urgent','maintenance','event'] as $t)
                        <option value="{{ $t }}" {{ ($isActive ? old('type') : $ann->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Role</label>
                    <select name="target_role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        @foreach(['resident','admin','security_head'] as $r)
                        <option value="{{ $r }}" {{ ($isActive ? old('target_role') : $ann->target_role) === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="expires_at"
                    value="{{ $isActive ? old('expires_at') : $ann->expires_at?->format('Y-m-d\TH:i') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>

<x-modal name="delete-ann-{{ $ann->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Announcement</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border line-clamp-2">
                "{{ $ann->title }}"
            </p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.announcements.destroy', $ann) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>

@endforeach
@endsection
