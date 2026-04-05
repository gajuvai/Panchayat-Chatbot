@extends('layouts.app')
@section('title', 'New Announcement')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.announcements.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">New Announcement</h1>

        <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('type') border-red-400 @enderror">
                        @foreach(['general','urgent','maintenance','event'] as $t)
                        <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Create Announcement
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
