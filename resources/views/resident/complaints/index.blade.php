@extends('layouts.app')
@section('title', 'My Complaints')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-gray-500 text-sm">{{ $complaints->total() }} complaint(s) found</p>
        <a href="{{ route('resident.complaints.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + New Complaint
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200 transition">Filter</button>
        <a href="{{ route('resident.complaints.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    {{-- Complaints list --}}
    @forelse($complaints as $complaint)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs text-gray-500">{{ $complaint->complaint_number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $complaint->priority->badgeClass() }}">{{ $complaint->priority->label() }}</span>
                </div>
                <h3 class="font-semibold text-gray-800 mt-1">{{ $complaint->title }}</h3>
                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $complaint->description }}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                    <span>{{ $complaint->category?->name }}</span>
                    <span>{{ $complaint->created_at->diffForHumans() }}</span>
                    @if($complaint->location)
                    <span>📍 {{ $complaint->location }}</span>
                    @endif
                    @if($complaint->upvotes > 0)
                    <span>👍 {{ $complaint->upvotes }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('resident.complaints.show', $complaint) }}" class="flex-shrink-0 text-indigo-600 text-sm hover:underline">View →</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-gray-500 text-sm">No complaints yet.</p>
        <a href="{{ route('resident.complaints.create') }}" class="mt-2 inline-block text-indigo-600 text-sm hover:underline">File your first complaint</a>
    </div>
    @endforelse

    {{ $complaints->links() }}
</div>
@endsection
