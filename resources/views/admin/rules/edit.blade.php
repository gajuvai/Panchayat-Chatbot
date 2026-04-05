@extends('layouts.app')
@section('title', 'Edit — ' . $rule->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.rules.show', $rule) }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Edit Rule Book Section</h1>

        <form action="{{ route('admin.rules.update', $rule) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $rule->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                <textarea name="content" rows="10"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('content') border-red-400 @enderror">{{ old('content', $rule->content) }}</textarea>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                    <input type="number" name="section_order" value="{{ old('section_order', $rule->section_order) }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('section_order') border-red-400 @enderror">
                    @error('section_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end pb-0.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1"
                            class="rounded border-gray-300 text-indigo-600"
                            {{ old('is_published', $rule->is_published) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Published</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.rules.show', $rule) }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
