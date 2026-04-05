@extends('layouts.app')
@section('title', $announcement->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('announcements.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back</a>
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-xs px-2 py-0.5 rounded {{ $announcement->typeBadgeClass() }}">{{ ucfirst($announcement->type) }}</span>
            <span class="text-xs text-gray-400">{{ $announcement->published_at?->format('d M Y, h:i A') }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $announcement->title }}</h1>
        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($announcement->body)) !!}</div>
        <p class="text-xs text-gray-400 mt-6 border-t pt-3">Posted by {{ $announcement->author->name }}</p>
    </div>
</div>
@endsection
