@extends('layouts.app')
@section('title', 'Society Rule Book')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="bg-gradient-to-r from-indigo-700 to-indigo-900 text-white rounded-xl p-6 mb-6">
        <h2 class="text-2xl font-bold mb-1">📖 Society Rule Book</h2>
        <p class="text-indigo-200 text-sm">Community guidelines, regulations and policies</p>
    </div>

    @forelse($sections as $section)
    <div class="bg-white rounded-xl border p-6 hover:shadow-sm transition">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ $section->section_order }}. {{ $section->title }}</h3>
        <div class="text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none">{!! $section->content !!}</div>
        <p class="text-xs text-gray-400 mt-4">Last updated {{ $section->updated_at->format('d M Y') }}</p>
    </div>
    @empty
    <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
        <p>Rule book sections will be added soon.</p>
    </div>
    @endforelse
</div>
@endsection
