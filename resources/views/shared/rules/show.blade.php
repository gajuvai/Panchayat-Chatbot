@extends('layouts.app')
@section('title', $section->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <a href="{{ route('rules.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Rule Book</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Section {{ $section->section_order }}</span>
            <span class="text-xs text-gray-400">Last updated {{ $section->updated_at->format('d M Y') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $section->title }}</h1>

        <div class="text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none">
            {!! nl2br(e($section->content)) !!}
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('rules.index') }}" class="text-sm text-indigo-600 hover:underline">View all rules</a>
    </div>
</div>
@endsection
