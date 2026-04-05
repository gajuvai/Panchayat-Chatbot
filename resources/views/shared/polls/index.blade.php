@extends('layouts.app')
@section('title', 'Polls & Votes')

@section('content')
<div class="space-y-4">
    @forelse($polls as $poll)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    @if($poll->isActiveNow())
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Active</span>
                    @else
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">Closed</span>
                    @endif
                    <span class="text-xs text-gray-500">{{ $poll->options->count() }} options</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $poll->title }}</h3>
                @if($poll->description)<p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $poll->description }}</p>@endif
                <p class="text-xs text-gray-400 mt-2">Ends: {{ $poll->ends_at->format('d M Y, h:i A') }}</p>
            </div>
            <a href="{{ route('polls.show', $poll) }}" class="flex-shrink-0 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                {{ $poll->hasUserVoted(auth()->user()) ? 'View Results' : 'Vote Now' }}
            </a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <p class="text-gray-400">No active polls at the moment.</p>
    </div>
    @endforelse
    {{ $polls->links() }}
</div>
@endsection
