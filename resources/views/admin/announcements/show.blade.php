@extends('layouts.app')
@section('title', $announcement->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.announcements.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded {{ $announcement->typeBadgeClass() }}">{{ ucfirst($announcement->type) }}</span>
            @if($announcement->is_published)
                <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
            @else
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
            @endif
            <span class="text-xs text-gray-400">{{ $announcement->created_at->format('d M Y, h:i A') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $announcement->title }}</h1>
        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($announcement->body)) !!}</div>

        <div class="border-t mt-6 pt-4 text-xs text-gray-400 space-y-1">
            <p>Posted by {{ $announcement->author->name }}</p>
            @if($announcement->target_role)<p>Target: {{ ucfirst(str_replace('_', ' ', $announcement->target_role)) }}</p>@endif
            @if($announcement->expires_at)<p>Expires: {{ $announcement->expires_at->format('d M Y') }}</p>@endif
        </div>

        <div class="flex gap-3 mt-4">
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</a>
            <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST">
                @csrf @method('PATCH')
                <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition">
                    {{ $announcement->is_published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST"
                onsubmit="return confirm('Delete this announcement?')">
                @csrf @method('DELETE')
                <button class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
