@extends('layouts.app')
@section('title', $rule->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('admin.rules.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Rule Book</a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Section {{ $rule->section_order }}</span>
            @if($rule->is_published)
                <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Published</span>
            @else
                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Draft</span>
            @endif
            <span class="text-xs text-gray-400">{{ $rule->created_at->format('d M Y') }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ $rule->title }}</h1>

        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
            {!! nl2br(e($rule->content)) !!}
        </div>

        <div class="border-t mt-6 pt-4 text-xs text-gray-400">
            <p>Written by {{ $rule->author?->name ?? 'Unknown' }}</p>
            @if($rule->updated_at->ne($rule->created_at))
            <p class="mt-0.5">Last updated {{ $rule->updated_at->format('d M Y, h:i A') }}</p>
            @endif
        </div>

        <div class="flex gap-3 mt-5">
            <a href="{{ route('admin.rules.edit', $rule) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</a>
            <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST"
                onsubmit="return confirm('Delete this section permanently?')">
                @csrf @method('DELETE')
                <button class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
