@extends('errors.layout')
@section('code', '503')
@section('title', 'Service Unavailable')

@section('body')
<div class="text-[9rem] font-black text-purple-100 leading-none select-none -mb-6">503</div>

<div class="mx-auto w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Under Maintenance</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-2 max-w-sm mx-auto">
    {{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Panchayat is currently down for scheduled maintenance.' }}
</p>
<p class="text-gray-400 text-xs mb-8 max-w-sm mx-auto">
    We'll be back shortly. Thank you for your patience.
</p>

@if(isset($exception) && method_exists($exception, 'retryAfter') && $exception->retryAfter)
<div class="mb-6 inline-flex items-center gap-2 bg-purple-50 border border-purple-200 text-purple-700 text-sm px-4 py-2 rounded-xl">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Back at {{ $exception->retryAfter->format('h:i A') }}
</div>
@endif

{{-- Animated pulse to show we're working on it --}}
<div class="flex items-center justify-center gap-1.5 text-gray-400 text-xs mt-4">
    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
    <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
    <span class="ml-2">Working on it</span>
</div>
@endsection
