@extends('layouts.app')
@section('title', 'Community Forum')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-gray-500 text-sm">{{ $threads->total() }} thread(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-thread' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Thread
        </button>
    </div>

    <form method="GET" class="bg-white rounded-xl border p-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search threads..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200 transition">Search</button>
        @if(request('search'))
        <a href="{{ route('forum.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
        @endif
    </form>

    @forelse($threads as $thread)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition {{ $thread->is_pinned ? 'border-l-4 border-l-indigo-400' : '' }}">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    @if($thread->is_pinned)
                    <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">Pinned</span>
                    @endif
                    @if($thread->is_locked)
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Locked</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $thread->author->name }} · {{ $thread->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $thread->title }}</h3>
                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ strip_tags($thread->body) }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $thread->replies_count ?? $thread->replies->count() }} replies
                    @if($thread->last_reply_at) · Last reply {{ $thread->last_reply_at->diffForHumans() }} @endif
                </p>
            </div>
            <a href="{{ route('forum.show', $thread) }}" class="flex-shrink-0 text-indigo-600 text-sm hover:underline">View →</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <p class="text-gray-400">No threads yet. Start the conversation!</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-thread' }))"
            class="mt-2 inline-block text-indigo-600 text-sm hover:underline">Create first thread</button>
    </div>
    @endforelse

    {{ $threads->links() }}
</div>

{{-- Create Thread Modal --}}
<x-modal name="create-thread" :show="$errors->any()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Start a New Thread</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('forum.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="What's your question or topic?">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="7"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror"
                    placeholder="Describe your topic in detail...">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Post Thread
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endsection
