@extends('layouts.app')
@section('title', 'Create Poll')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.polls.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Polls</a>

    <div class="bg-white rounded-xl border p-6">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Create New Poll</h1>

        <form action="{{ route('admin.polls.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Question / Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('title') border-red-400 @enderror"
                    placeholder="e.g. Which park improvement do you prefer?">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Description <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('description') border-red-400 @enderror"
                    placeholder="Additional context for residents...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Dynamic Options with Alpine.js --}}
            <div x-data="{
                options: {{ json_encode(old('options', ['', ''])) }},
                addOption() { this.options.push(''); },
                removeOption(index) {
                    if (this.options.length > 2) this.options.splice(index, 1);
                }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                @error('options')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror

                <div class="space-y-2">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 text-sm w-5 text-right flex-shrink-0" x-text="index + 1 + '.'"></span>
                            <input type="text" :name="'options[' + index + ']'" x-model="options[index]"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                :placeholder="'Option ' + (index + 1)">
                            <button type="button" @click="removeOption(index)"
                                class="text-gray-300 hover:text-red-400 transition flex-shrink-0"
                                :class="{ 'opacity-30 cursor-not-allowed': options.length <= 2 }"
                                :disabled="options.length <= 2"
                                title="Remove option">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ends At <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="date" name="ends_at" value="{{ old('ends_at') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('ends_at') border-red-400 @enderror">
                    @error('ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    Create Poll
                </button>
                <a href="{{ route('admin.polls.index') }}" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
