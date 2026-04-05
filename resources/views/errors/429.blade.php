@extends('errors.layout')
@section('code', '429')
@section('title', 'Too Many Requests')

@section('body')
<div class="text-[9rem] font-black text-amber-100 leading-none select-none -mb-6">429</div>

<div class="mx-auto w-20 h-20 bg-amber-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Too Many Requests</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-2 max-w-sm mx-auto">
    You've sent too many requests in a short period. Please slow down and wait a moment before trying again.
</p>

@if(isset($exception) && method_exists($exception, 'getHeaders') && isset($exception->getHeaders()['Retry-After']))
<p class="text-amber-600 text-sm font-medium mb-6">
    You can retry in <strong>{{ $exception->getHeaders()['Retry-After'] }} seconds</strong>.
</p>
@else
<p class="text-gray-400 text-xs mb-8">Please wait a minute before trying again.</p>
@endif

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <button onclick="history.back()"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Go Back
    </button>
    <a href="{{ url('/') }}"
        class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl transition">
        Home
    </a>
</div>
@endsection
