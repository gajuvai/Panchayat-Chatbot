@extends('layouts.app')
@section('title', $poll->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('polls.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to Polls</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-2">
            @if($poll->isActiveNow())
                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Active</span>
            @else
                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">Closed</span>
            @endif
            <span class="text-xs text-gray-500">Ends {{ $poll->ends_at->format('d M Y, h:i A') }}</span>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $poll->title }}</h2>
        @if($poll->description)
        <p class="text-sm text-gray-600 mb-4">{{ $poll->description }}</p>
        @endif

        @if(!$hasVoted && $poll->isActiveNow())
        {{-- Voting form --}}
        <form method="POST" action="{{ route('polls.vote', $poll) }}">
            @csrf
            <div class="space-y-2 mb-4">
                @foreach($poll->options as $option)
                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 peer-checked:border-indigo-500 transition">
                    <input type="radio" name="option_id" value="{{ $option->id }}" class="text-indigo-600" required>
                    <span class="text-sm text-gray-800">{{ $option->option_text }}</span>
                </label>
                @endforeach
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Submit Vote</button>
        </form>
        @elseif($canShowResults)
        {{-- Results --}}
        <div class="space-y-3">
            @php $total = $poll->getTotalVotes(); @endphp
            @foreach($poll->options as $option)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">{{ $option->option_text }}</span>
                    <span class="font-medium text-gray-600">{{ $option->vote_count }} ({{ $option->percentage }}%)</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ $option->percentage }}%"></div>
                </div>
            </div>
            @endforeach
            <p class="text-xs text-gray-400 mt-2">{{ $total }} total vote(s)</p>
        </div>
        @else
        <p class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4">
            {{ $hasVoted ? 'You have already voted. Results will be shown after the poll closes.' : 'Results will be shown after the poll closes.' }}
        </p>
        @endif
    </div>
</div>
@endsection
