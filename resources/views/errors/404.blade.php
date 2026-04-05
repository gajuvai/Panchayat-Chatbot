@extends('errors.layout')
@section('code', '404')
@section('title', 'Page Not Found')

@section('body')
{{-- Code --}}
<div class="text-[9rem] font-black text-indigo-100 leading-none select-none -mb-6">404</div>

{{-- Icon --}}
<div class="mx-auto w-20 h-20 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Page Not Found</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
    The page you're looking for doesn't exist or may have been moved. Double-check the URL or head back home.
</p>

{{-- Actions --}}
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

{{-- Helpful links --}}
<div class="mt-10 border-t border-gray-100 pt-8">
    <p class="text-xs text-gray-400 mb-4">You might be looking for:</p>
    <div class="flex flex-wrap items-center justify-center gap-2">
        @auth
            <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="text-xs text-indigo-600 hover:underline bg-indigo-50 px-3 py-1.5 rounded-lg">Dashboard</a>
            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:underline bg-indigo-50 px-3 py-1.5 rounded-lg">Notifications</a>
            <a href="{{ route('announcements.index') }}" class="text-xs text-indigo-600 hover:underline bg-indigo-50 px-3 py-1.5 rounded-lg">Announcements</a>
        @else
            <a href="{{ route('login') }}" class="text-xs text-indigo-600 hover:underline bg-indigo-50 px-3 py-1.5 rounded-lg">Sign In</a>
            <a href="{{ route('register') }}" class="text-xs text-indigo-600 hover:underline bg-indigo-50 px-3 py-1.5 rounded-lg">Register</a>
        @endauth
    </div>
</div>
@endsection
