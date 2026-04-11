@extends('layouts.app')
@section('title', $poll->title)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <a href="{{ route('admin.polls.index') }}" class="text-indigo-600 text-sm hover:underline inline-block">← Back to Polls</a>

    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-center gap-2 flex-wrap">
                @if(!$poll->is_active)
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Inactive</span>
                @elseif($poll->ends_at && $poll->ends_at->isPast())
                    <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-600">Ended</span>
                @else
                    <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                @endif
                <span class="text-xs text-gray-400">{{ $poll->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-poll' }))"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Edit</button>
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-poll' }))"
                    class="border border-red-200 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $poll->title }}</h1>
        @if($poll->description)
            <p class="text-sm text-gray-600 mb-4">{{ $poll->description }}</p>
        @endif
        <div class="text-xs text-gray-400 space-y-0.5 mb-4">
            <p>Created by {{ $poll->creator->name ?? '—' }}</p>
            @if($poll->ends_at)
                <p>Ends: {{ $poll->ends_at->format('d M Y') }}</p>
            @else
                <p>No end date</p>
            @endif
            <p>Total votes: <span class="font-semibold text-gray-600">{{ $totalVotes }}</span></p>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white rounded-xl border p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Results</h2>
        @if($poll->options->isEmpty())
            <p class="text-sm text-gray-400">No options defined.</p>
        @else
            <div class="space-y-4">
                @foreach($poll->options as $option)
                @php $pct = $totalVotes > 0 ? round(($option->vote_count / $totalVotes) * 100, 1) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700">{{ $option->option_text }}</span>
                        <span class="text-xs text-gray-500 font-medium">
                            {{ $option->vote_count }} vote{{ $option->vote_count !== 1 ? 's' : '' }} ({{ $pct }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="bg-indigo-500 h-2.5 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @if($totalVotes === 0)
                <p class="text-xs text-gray-400 mt-4 text-center">No votes cast yet.</p>
            @endif
        @endif
    </div>
</div>

{{-- Edit Poll Modal --}}
@php $existingOptions = old('options', $poll->options->pluck('option_text')->toArray()); @endphp
<x-modal name="edit-poll" :show="$errors->isNotEmpty()" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Edit Poll</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.polls.update', $poll) }}" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Question / Title</label>
                <input type="text" name="title" value="{{ old('title', $poll->title) }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $poll->description) }}</textarea>
            </div>
            <div x-data="{
                options: {{ json_encode($existingOptions) }},
                addOption() { this.options.push(''); },
                removeOption(index) { if (this.options.length > 2) this.options.splice(index, 1); }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                @if($poll->votes()->count() > 0)
                <p class="text-xs text-yellow-600 bg-yellow-50 border border-yellow-200 rounded px-3 py-2 mb-2">
                    This poll has votes. Editing options will preserve vote counts for matching option text.
                </p>
                @endif
                @error('options')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
                <div class="space-y-2">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 text-sm w-5 text-right flex-shrink-0" x-text="index + 1 + '.'"></span>
                            <input type="text" :name="'options[' + index + ']'" x-model="options[index]"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                :placeholder="'Option ' + (index + 1)">
                            <button type="button" @click="removeOption(index)"
                                :disabled="options.length <= 2"
                                :class="{ 'opacity-30 cursor-not-allowed': options.length <= 2 }"
                                class="text-gray-300 hover:text-red-400 transition flex-shrink-0">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addOption()" class="mt-3 text-indigo-600 text-sm hover:text-indigo-800 font-medium flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Option
                </button>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ends At <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" name="ends_at" value="{{ old('ends_at', $poll->ends_at?->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $poll->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save Changes</button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Delete Modal --}}
<x-modal name="delete-poll" maxWidth="sm">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="px-6 py-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Delete Poll</h3>
                    <p class="text-sm text-gray-500 mt-0.5">All votes will also be deleted.</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $poll->title }}</p>
        </div>
        <div class="flex items-center gap-3 px-6 pb-5">
            <form action="{{ route('admin.polls.destroy', $poll) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Delete</button>
            </form>
            <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
        </div>
    </div>
</x-modal>
@endsection
