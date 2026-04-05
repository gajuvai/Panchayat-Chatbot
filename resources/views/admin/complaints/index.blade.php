@extends('layouts.app')
@section('title', 'Manage Complaints')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $complaints->total() }} complaint(s)</p>
        <a href="{{ route('admin.analytics.export') }}" class="border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg text-sm hover:bg-gray-50 transition">⬇ Export CSV</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or #..."
            class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-48">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(['open','in_progress','resolved','closed','rejected'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Priorities</option>
            @foreach(['low','medium','high','urgent'] as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.complaints.index') }}" class="text-gray-500 text-sm py-1.5">Clear</a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">#</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Resident</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Priority</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Assigned</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $complaint->complaint_number }}</td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-800 line-clamp-1">{{ $complaint->title }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $complaint->user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $complaint->category?->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $complaint->status->badgeClass() }}">{{ $complaint->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $complaint->priority->badgeClass() }}">{{ $complaint->priority->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $complaint->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $complaint->created_at->format('d M') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="text-indigo-600 hover:underline text-xs font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No complaints found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $complaints->links() }}
</div>
@endsection
