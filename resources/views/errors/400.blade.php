@extends('errors.layout')
@section('code', '400')
@section('title', 'Bad Request')

@section('body')
<div class="text-[9rem] font-black text-gray-200 leading-none select-none -mb-6">400</div>

<div class="mx-auto w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Bad Request</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
    The server couldn't understand your request due to invalid syntax or missing information. Please go back and try again.
</p>

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
