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
            <a href="{{ route('forum.edit', $forum) }}" class="text-indigo-600 text-sm hover:underline">Edit</a>
            <form action="{{ route('forum.destroy', $forum) }}" method="POST"
                onsubmit="return confirm('Delete this thread?')">
                @csrf @method('DELETE')
                <button class="text-red-500 text-sm hover:underline">Delete</button>
            </form>
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
                <form action="{{ route('replies.destroy', $reply) }}" method="POST"
                    onsubmit="return confirm('Delete reply?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-400 hover:underline">Delete</button>
                </form>
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
@endsection
