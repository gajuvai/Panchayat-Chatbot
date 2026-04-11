@extends('layouts.app')
@section('title', $rule->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('admin.rules.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Rule Book</a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Section {{ $rule->section_order }}</span>
            @if($rule->is_published)
                <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
            @else
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
            @endif
            <span class="text-xs text-gray-400">{{ $rule->created_at->format('d M Y') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $rule->title }}</h1>
        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">{!! nl2br(e($rule->content)) !!}</div>

        <div class="border-t mt-6 pt-4 text-xs text-gray-400">
            <p>Written by {{ $rule->author?->name ?? 'Unknown' }}</p>
            @if($rule->updated_at->ne($rule->created_at))
            <p class="mt-0.5">Last updated {{ $rule->updated_at->format('d M Y, h:i A') }}</p>
            @endif
        </div>

        <div class="flex gap-3 mt-5">
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-rule' }))"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</button>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-rule' }))"
                class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<x-modal name="edit-rule" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Rule Book Section</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.rules.update', $rule) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $rule->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                <textarea name="content" rows="8"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('content') border-red-400 @enderror">{{ old('content', $rule->content) }}</textarea>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Number</label>
                    <input type="number" name="section_order" value="{{ old('section_order', $rule->section_order) }}" min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1"
                            class="rounded border-gray-300 text-indigo-600"
                            {{ old('is_published', $rule->is_published) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700">Published</span>
                    </label>
                </div>
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
<x-modal name="delete-rule" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Section</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $rule->title }}</p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
