@extends('layouts.app')
@section('title', $announcement->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.announcements.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded {{ $announcement->typeBadgeClass() }}">{{ ucfirst($announcement->type) }}</span>
            @if($announcement->is_published)
                <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
            @else
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
            @endif
            <span class="text-xs text-gray-400">{{ $announcement->created_at->format('d M Y, h:i A') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $announcement->title }}</h1>
        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($announcement->body)) !!}</div>

        <div class="border-t mt-6 pt-4 text-xs text-gray-400 space-y-1">
            <p>Posted by {{ $announcement->author->name }}</p>
            @if($announcement->target_role)<p>Target: {{ ucfirst(str_replace('_', ' ', $announcement->target_role)) }}</p>@endif
            @if($announcement->expires_at)<p>Expires: {{ $announcement->expires_at->format('d M Y') }}</p>@endif
        </div>

        <div class="flex gap-3 mt-4">
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-announcement' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</button>
            <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST">
                @csrf @method('PATCH')
                <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    {{ $announcement->is_published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-announcement' }))"
                class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<x-modal name="edit-announcement" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Announcement</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror">{{ old('body', $announcement->body) }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['general','urgent','maintenance','event'] as $t)
                        <option value="{{ $t }}" {{ old('type', $announcement->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Role</label>
                    <select name="target_role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        @foreach(['resident','admin','security_head'] as $r)
                        <option value="{{ $r }}" {{ old('target_role', $announcement->target_role) === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="expires_at"
                    value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
<x-modal name="delete-announcement" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Announcement</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium line-clamp-2">"{{ $announcement->title }}"</p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
