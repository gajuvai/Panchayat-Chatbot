@extends('layouts.app')
@section('title', 'Community Forum')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-gray-500 text-sm">{{ $threads->total() }} thread(s)</p>
        <a href="{{ route('forum.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + New Thread
        </a>
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
        <a href="{{ route('forum.create') }}" class="mt-2 inline-block text-indigo-600 text-sm hover:underline">Create first thread</a>
    </div>
    @endforelse

    {{ $threads->links() }}
</div>
@endsection
