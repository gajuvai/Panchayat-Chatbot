@extends('layouts.app')
@section('title', 'New Thread')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('forum.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Forum</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Start a New Thread</h1>

        <form action="{{ route('forum.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror"
                    placeholder="What's your question or topic?">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" rows="8"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('body') border-red-400 @enderror"
                    placeholder="Describe your topic in detail...">{{ old('body') }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Post Thread
                </button>
                <a href="{{ route('forum.index') }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
