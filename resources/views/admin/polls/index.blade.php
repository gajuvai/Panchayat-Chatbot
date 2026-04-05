@extends('layouts.app')
@section('title', 'Polls')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $polls->total() }} poll(s)</p>
        <a href="{{ route('admin.polls.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + Create Poll
        </a>
    </div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Options</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Total Votes</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Ends At</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($polls as $poll)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $poll->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $poll->options_count }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $poll->votes_count }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        {{ $poll->ends_at ? $poll->ends_at->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if(!$poll->is_active)
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Inactive</span>
                        @elseif($poll->ends_at && $poll->ends_at->isPast())
                            <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-600">Ended</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.polls.show', $poll) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.polls.edit', $poll) }}" class="text-gray-500 hover:underline text-xs">Edit</a>
                            <form action="{{ route('admin.polls.destroy', $poll) }}" method="POST"
                                onsubmit="return confirm('Delete this poll and all its votes?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No polls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $polls->links() }}
</div>
@endsection
