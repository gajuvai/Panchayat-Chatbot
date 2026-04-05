@extends('errors.layout')
@section('code', '401')
@section('title', 'Unauthenticated')

@section('body')
<div class="text-[9rem] font-black text-yellow-100 leading-none select-none -mb-6">401</div>

<div class="mx-auto w-20 h-20 bg-yellow-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Authentication Required</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
    You need to be signed in to access this page. Please log in with your community account to continue.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <a href="{{ route('login') }}"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
        </svg>
        Sign In
    </a>
    <a href="{{ route('register') }}"
        class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-xl transition">
        Create Account
    </a>
</div>
@endsection
