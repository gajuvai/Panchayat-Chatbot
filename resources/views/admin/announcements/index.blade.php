@extends('layouts.app')
@section('title', 'Announcements')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $announcements->total() }} announcement(s)</p>
        <a href="{{ route('admin.announcements.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + New Announcement
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Target</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $ann)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $ann->title }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded {{ $ann->typeBadgeClass() }}">{{ ucfirst($ann->type) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $ann->target_role ? ucfirst(str_replace('_', ' ', $ann->target_role)) : 'All' }}</td>
                    <td class="px-4 py-3">
                        @if($ann->is_published)
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $ann->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.announcements.show', $ann) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.announcements.edit', $ann) }}" class="text-gray-500 hover:underline text-xs">Edit</a>
                            <form action="{{ route('admin.announcements.publish', $ann) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs {{ $ann->is_published ? 'text-yellow-600' : 'text-green-600' }} hover:underline">
                                    {{ $ann->is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.announcements.destroy', $ann) }}" method="POST"
                                onsubmit="return confirm('Delete this announcement?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $announcements->links() }}
</div>
@endsection
