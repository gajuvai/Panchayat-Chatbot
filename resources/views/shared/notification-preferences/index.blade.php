@extends('layouts.app')
@section('title', 'Notification Preferences')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Notification Preferences</h1>
        <p class="text-sm text-gray-500 mt-0.5">Control which notifications you receive.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <form action="{{ route('notifications.preferences.update') }}" method="POST">
        @csrf @method('PATCH')

        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Notification Types</h2>
                <p class="text-xs text-gray-400 mt-0.5">Emergency alerts are always sent and cannot be disabled.</p>
            </div>

            <div class="divide-y">
                @foreach($types as $type => $label)
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $label }}</p>
                        <p class="text-xs text-gray-400">{{ $preferences[$type] === 'off' ? 'Disabled' : 'Instant notifications' }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer" x-data>
                        <input type="hidden" name="preferences[{{ $type }}]" value="off">
                        <input type="checkbox" name="preferences[{{ $type }}]" value="instant"
                            class="sr-only peer"
                            {{ $preferences[$type] === 'instant' ? 'checked' : '' }}
                            @change="$el.previousElementSibling.value = $el.checked ? 'instant' : 'off'">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                            peer-checked:after:translate-x-full peer-checked:after:border-white
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:border-gray-300 after:border after:rounded-full
                            after:h-5 after:w-5 after:transition-all
                            peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                @endforeach
            </div>

            {{-- Emergency alerts — always on --}}
            <div class="flex items-center justify-between px-5 py-4 bg-red-50 border-t border-red-100">
                <div>
                    <p class="text-sm font-medium text-red-800">Emergency Alerts</p>
                    <p class="text-xs text-red-400">Always enabled — cannot be turned off.</p>
                </div>
                <div class="w-11 h-6 bg-red-400 rounded-full flex items-center justify-end pr-1">
                    <div class="w-4 h-4 bg-white rounded-full"></div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Save Preferences
            </button>
        </div>
    </form>
</div>
@endsection
