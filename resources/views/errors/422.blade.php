@extends('errors.layout')
@section('code', '422')
@section('title', 'Unprocessable Content')

@section('body')
<div class="text-[9rem] font-black text-orange-100 leading-none select-none -mb-6">422</div>

<div class="mx-auto w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
    <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-2">Unprocessable Content</h1>
<p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
    The data you submitted could not be processed. Please go back, check your inputs, and try again.
</p>

<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
    <button onclick="history.back()"
        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Go Back
    </button>
</div>
@endsection
