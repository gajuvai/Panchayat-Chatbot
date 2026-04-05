@extends('layouts.app')
@section('title', 'Edit Announcement')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.announcements.show', $announcement) }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Edit Announcement</h1>

        <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('body') border-red-400 @enderror">{{ old('body', $announcement->body) }}</textarea>
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
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.announcements.show', $announcement) }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
