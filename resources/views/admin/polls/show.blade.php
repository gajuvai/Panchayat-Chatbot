@extends('layouts.app')
@section('title', $poll->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('admin.polls.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Polls</a>

    <div class="bg-white rounded-xl border p-6">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-center gap-2 flex-wrap">
                @if(!$poll->is_active)
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Inactive</span>
                @elseif($poll->ends_at && $poll->ends_at->isPast())
                    <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-600">Ended</span>
                @else
                    <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                @endif
                <span class="text-xs text-gray-400">{{ $poll->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <a href="{{ route('admin.polls.edit', $poll) }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</a>
                <form action="{{ route('admin.polls.destroy', $poll) }}" method="POST"
                    onsubmit="return confirm('Delete this poll and all votes? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
                </form>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $poll->title }}</h1>

        @if($poll->description)
            <p class="text-sm text-gray-600 mb-4">{{ $poll->description }}</p>
        @endif

        <div class="text-xs text-gray-400 space-y-0.5 mb-4">
            <p>Created by {{ $poll->creator->name ?? '—' }}</p>
            @if($poll->ends_at)
                <p>Ends: {{ $poll->ends_at->format('d M Y') }}</p>
            @else
                <p>No end date</p>
            @endif
            <p>Total votes: <span class="font-semibold text-gray-600">{{ $totalVotes }}</span></p>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-xl border p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Results</h2>

        @if($poll->options->isEmpty())
            <p class="text-sm text-gray-400">No options defined.</p>
        @else
            <div class="space-y-4">
                @foreach($poll->options as $option)
                @php
                    $pct = $totalVotes > 0 ? round(($option->vote_count / $totalVotes) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700">{{ $option->option_text }}</span>
                        <span class="text-xs text-gray-500 font-medium">
                            {{ $option->vote_count }} vote{{ $option->vote_count !== 1 ? 's' : '' }}
                            ({{ $pct }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full transition-all duration-300"
                            style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($totalVotes === 0)
                <p class="text-xs text-gray-400 mt-4 text-center">No votes cast yet.</p>
            @endif
        @endif
    </div>
</div>
@endsection
