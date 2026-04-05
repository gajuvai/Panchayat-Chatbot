@extends('errors.layout')
@section('code', '419')
@section('title', 'Session Expired')

@section('body')
<div class="text-[9rem] font-black text-orange-100 leading-none select-none -mb-6">419</div>

<div class="mx-auto w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Session Expired</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-2 max-w-sm mx-auto">
    Your session has expired or the security token is invalid. This usually happens after being idle for too long or opening the page in multiple tabs.
</p>
<p class="text-gray-400 text-xs mb-8 max-w-sm mx-auto">
    Simply go back and try your action again — your work should still be there.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <button onclick="history.back()"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Go Back & Retry
    </button>
    <a href="{{ url('/') }}"
        class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl transition">
        Home
    </a>
</div>

<div class="mt-8 bg-blue-50 border border-blue-200 text-blue-700 text-xs px-4 py-3 rounded-xl max-w-sm mx-auto text-left">
    <strong class="block mb-1">Why does this happen?</strong>
    For security, form submissions expire after a period of inactivity. Refreshing or going back will give you a fresh token.
</div>
@endsection
