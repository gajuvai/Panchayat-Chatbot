@extends('errors.layout')
@section('code', '403')
@section('title', 'Access Denied')

@section('body')
<div class="text-[9rem] font-black text-red-100 leading-none select-none -mb-6">403</div>

<div class="mx-auto w-20 h-20 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Access Denied</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-2 max-w-sm mx-auto">
    You don't have permission to view this page.
    @if($exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.')
        <br><span class="text-xs text-gray-400 mt-1 block">{{ $exception->getMessage() }}</span>
    @endif
</p>
<p class="text-gray-400 text-xs mb-8 max-w-sm mx-auto">
    If you believe this is a mistake, contact your society administrator.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ url('/') }}"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Go to Home
    </a>
    <button onclick="history.back()"
        class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Go Back
    </button>
</div>

@guest
<div class="mt-8 bg-amber-50 border border-amber-200 text-amber-700 text-xs px-4 py-3 rounded-xl max-w-sm mx-auto">
    <strong>Not signed in?</strong> Some pages require authentication.
    <a href="{{ route('login') }}" class="underline font-medium ml-1">Sign in here</a>
</div>
@endguest
@endsection
