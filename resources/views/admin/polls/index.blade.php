@extends('layouts.app')
@section('title', 'Polls')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $polls->total() }} poll(s)</p>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-poll' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Poll
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Options</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Total Votes</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Ends At</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($polls as $poll)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $poll->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $poll->options_count }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $poll->votes_count }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $poll->ends_at ? $poll->ends_at->format('d M Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        @if(!$poll->is_active)
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500">Inactive</span>
                        @elseif($poll->ends_at && $poll->ends_at->isPast())
                            <span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-600">Ended</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Active</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.polls.show', $poll) }}" class="text-indigo-600 hover:underline text-xs">View</a>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-poll-{{ $poll->id }}' }))"
                                class="text-gray-500 hover:underline text-xs">Edit</button>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-poll-{{ $poll->id }}' }))"
                                class="text-red-400 hover:underline text-xs">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No polls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $polls->links() }}
</div>

{{-- Create Poll Modal --}}
<x-modal name="create-poll" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <h2 class="text-base font-semibold text-gray-800">Create New Poll</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('admin.polls.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Question / Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Which park improvement do you prefer?">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Additional context for residents...">{{ old('description') }}</textarea>
            </div>

            {{-- Dynamic Options --}}
            <div x-data="{
                options: {{ json_encode(old('options', ['', ''])) }},
                addOption() { this.options.push(''); },
                removeOption(index) { if (this.options.length > 2) this.options.splice(index, 1); }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Options <span class="text-red-500">*</span></label>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addOption()"
                    class="mt-3 text-indigo-600 text-sm hover:text-indigo-800 font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Option
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ends At <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" name="ends_at" value="{{ old('ends_at') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Active immediately</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Poll
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

{{-- Edit & Delete Modals per poll --}}
@foreach($polls as $poll)
@php
    $isActiveEdit = old('_edit_id') == $poll->id && $errors->any();
    $existingOptions = $isActiveEdit ? old('options', []) : $poll->options->pluck('option_text')->toArray();
@endphp

<x-modal name="edit-poll-{{ $poll->id }}" :show="old('_edit_id') == $poll->id && $errors->any()" maxWidth="xl">
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
            <input type="hidden" name="_edit_id" value="{{ $poll->id }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Question / Title</label>
                <input type="text" name="title" value="{{ $isActiveEdit ? old('title') : $poll->title }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ $isActiveEdit ? old('description') : $poll->description }}</textarea>
            </div>

            {{-- Dynamic Options --}}
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addOption()"
                    class="mt-3 text-indigo-600 text-sm hover:text-indigo-800 font-medium flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Option
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ends At <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" name="ends_at"
                        value="{{ $isActiveEdit ? old('ends_at') : $poll->ends_at?->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ ($isActiveEdit ? old('is_active') : ($poll->is_active ? '1' : '0')) == '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Changes
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>

<x-modal name="delete-poll-{{ $poll->id }}" maxWidth="sm">
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

@endforeach
@endsection
