@extends('layouts.app')
@section('title', 'Announcements')

@section('content')
<div class="space-y-4">
    @forelse($announcements as $ann)
    <div class="bg-white rounded-xl border p-5 {{ $ann->type === 'urgent' ? 'border-l-4 border-l-red-500' : '' }} hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs px-2 py-0.5 rounded {{ $ann->typeBadgeClass() }}">{{ ucfirst($ann->type) }}</span>
                    <span class="text-xs text-gray-400">{{ $ann->published_at?->format('d M Y') }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $ann->title }}</h3>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ strip_tags($ann->body) }}</p>
            </div>
            <a href="{{ route('announcements.show', $ann) }}" class="flex-shrink-0 text-indigo-600 text-sm hover:underline">Read →</a>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <p class="text-gray-400">No announcements yet.</p>
    </div>
    @endforelse
    {{ $announcements->links() }}
</div>
@endsection
