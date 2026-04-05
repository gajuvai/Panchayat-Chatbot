@extends('layouts.app')
@section('title', 'Trigger Emergency Alert')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('security.alerts.index') }}" class="text-indigo-600 text-sm hover:underline mb-4 inline-block">← Back to Alerts</a>

    {{-- Warning banner --}}
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-5 flex gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Important Notice</p>
            <p class="text-sm text-amber-700 mt-0.5">
                Triggering an emergency alert will immediately notify all residents and relevant
                authorities. Only use this for genuine emergencies. False alerts are a serious
                offence and may result in disciplinary action.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border-2 border-red-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Trigger Emergency Alert</h1>
        </div>

        <form action="{{ route('security.alerts.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alert Type <span class="text-red-500">*</span></label>
                <select name="alert_type"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none @error('alert_type') border-red-400 @enderror">
                    <option value="">— Select Alert Type —</option>
                    @foreach(['fire' => 'Fire', 'medical' => 'Medical Emergency', 'security' => 'Security Threat', 'natural_disaster' => 'Natural Disaster', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ old('alert_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('alert_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message / Details <span class="text-red-500">*</span></label>
                <textarea name="message" rows="5"
                    placeholder="Describe the emergency in detail — include the location, nature of the threat, and any immediate instructions for residents..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-400 focus:outline-none @error('message') border-red-400 @enderror">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Minimum 10 characters. Be as specific as possible.</p>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg text-sm font-bold uppercase tracking-widest transition"
                    onclick="return confirm('You are about to trigger an emergency alert. This action will notify all residents immediately. Are you sure?')">
                    Trigger Emergency Alert
                </button>
                <a href="{{ route('security.alerts.index') }}"
                    class="block text-center text-gray-500 text-sm mt-3 hover:text-gray-700">
                    Cancel — Return to Alerts
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
