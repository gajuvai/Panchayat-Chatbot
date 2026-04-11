@extends('layouts.app')
@section('title', $forum->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('forum.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Forum</a>

    {{-- Thread --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-2 flex-wrap">
            @if($forum->is_pinned)
            <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">Pinned</span>
            @endif
            @if($forum->is_locked)
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Locked</span>
            @endif
            <span class="text-xs text-gray-400">{{ $forum->author->name }} · {{ $forum->created_at->diffForHumans() }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $forum->title }}</h1>
        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($forum->body)) !!}</div>

        @if(auth()->id() === $forum->user_id && !$forum->is_locked)
        <div class="flex gap-3 mt-4 pt-4 border-t">
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-thread' }))"
                class="text-indigo-600 text-sm hover:underline">Edit</button>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-thread' }))"
                class="text-red-500 text-sm hover:underline">Delete</button>
        </div>
        @endif
    </div>

    {{-- Replies --}}
    <div class="space-y-3">
        <h2 class="font-semibold text-gray-700">{{ $forum->replies->count() }} Replies</h2>

        @foreach($forum->topReplies as $reply)
        <div class="bg-white rounded-xl border p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">{{ $reply->author->name }}</span>
                <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-700">{!! nl2br(e($reply->body)) !!}</p>
            @if($reply->is_solution)
            <span class="text-xs text-green-600 font-medium mt-1 inline-block">✓ Solution</span>
            @endif
            <div class="flex items-center gap-4 mt-2">
                <form action="{{ route('forum.replies.upvote', [$forum, $reply]) }}" method="POST">
                    @csrf
                    <button class="text-xs text-gray-500 hover:text-indigo-600">👍 {{ $reply->upvotes }}</button>
                </form>
                @if(auth()->id() === $reply->user_id)
                <a href="{{ route('replies.edit', $reply) }}" class="text-xs text-indigo-500 hover:underline">Edit</a>
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-reply-{{ $reply->id }}' }))"
                    class="text-xs text-red-400 hover:underline">Delete</button>
                @endif
            </div>

            {{-- Nested replies --}}
            @foreach($reply->children as $child)
            <div class="ml-6 mt-3 border-l-2 border-gray-100 pl-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-700">{{ $child->author->name }}</span>
                    <span class="text-xs text-gray-400">{{ $child->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-gray-700">{!! nl2br(e($child->body)) !!}</p>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Reply form --}}
    @if(!$forum->is_locked)
    <div class="bg-white rounded-xl border p-5">
        <h2 class="font-semibold text-gray-700 mb-3">Post a Reply</h2>
        @if(session('success'))
        <p class="text-green-600 text-sm mb-2">{{ session('success') }}</p>
        @endif
        <form action="{{ route('forum.replies.store', $forum) }}" method="POST" class="space-y-3">
            @csrf
            <textarea name="body" rows="4"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('body') border-red-400 @enderror"
                placeholder="Write your reply...">{{ old('body') }}</textarea>
            @error('body')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                Post Reply
            </button>
        </form>
    </div>
    @else
    <div class="bg-gray-50 rounded-xl border p-4 text-center text-sm text-gray-500">
        This thread is locked. No new replies allowed.
    </div>
    @endif
</div>

{{-- Edit Thread Modal --}}
@if(auth()->id() === $forum->user_id && !$forum->is_locked)
<x-modal name="edit-thread" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Thread</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('forum.update', $forum) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $forum->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="8"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror">{{ old('body', $forum->body) }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

<x-modal name="delete-thread" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Thread</h3>
                    <p class="text-sm text-gray-500 mt-0.5">All replies will also be deleted.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium line-clamp-2">{{ $forum->title }}</p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('forum.destroy', $forum) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endif

{{-- Delete Reply Modals --}}
@foreach($forum->topReplies as $reply)
@if(auth()->id() === $reply->user_id)
<x-modal name="delete-reply-{{ $reply->id }}" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Reply</h3>
                    <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('replies.destroy', $reply) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endif
@endforeach
@endsection
