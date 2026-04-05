@extends('errors.layout')
@section('code', '500')
@section('title', 'Server Error')

@section('body')
<div class="text-[9rem] font-black text-red-100 leading-none select-none -mb-6">500</div>

<div class="mx-auto w-20 h-20 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Internal Server Error</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-2 max-w-sm mx-auto">
    Something went wrong on our end. Our team has been notified and is working on a fix.
</p>
<p class="text-gray-400 text-xs mb-8">Please try again in a few minutes.</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ url('/') }}"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Go to Home
    </a>
    <button onclick="location.reload()"
        class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Retry
    </button>
</div>

{{-- Error reference for support --}}
<div class="mt-10 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 max-w-sm mx-auto text-left">
    <p class="text-xs text-gray-400 mb-1">Error reference</p>
    <p class="text-xs font-mono text-gray-600">{{ date('YmdHis') }}-500</p>
    <p class="text-[10px] text-gray-400 mt-1">Share this with support if the issue persists.</p>
</div>
@endsection
