@extends('layouts.app')
@section('title', 'Edit Reply')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('forum.show', $reply->thread_id) }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to thread</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Edit Reply</h1>

        <form action="{{ route('replies.update', $reply) }}" method="POST" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reply</label>
                <textarea name="body" rows="6"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('body') border-red-400 @enderror">{{ old('body', $reply->body) }}</textarea>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('forum.show', $reply->thread_id) }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
