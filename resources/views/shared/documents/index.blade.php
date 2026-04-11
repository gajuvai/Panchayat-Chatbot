@extends('layouts.app')
@section('title', 'Document Library')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Document Library</h1>
            <p class="text-sm text-gray-500 mt-0.5">Community documents, bylaws, forms & guidelines.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.documents.index') }}"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            Manage Documents
        </a>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row gap-5">

        {{-- Category sidebar --}}
        @if($categories->isNotEmpty())
        <div class="sm:w-48 flex-shrink-0">
            <div class="bg-white rounded-xl border overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50">
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Categories</h2>
                </div>
                <div class="divide-y">
                    <a href="{{ route('documents.browse') }}"
                        class="flex items-center justify-between px-4 py-2.5 text-sm {{ !request('category') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                        <span>All Documents</span>
                    </a>
                    @foreach($categories as $cat)
                    <a href="{{ route('documents.browse', ['category' => $cat->id]) }}"
                        class="flex items-center justify-between px-4 py-2.5 text-sm {{ request('category') == $cat->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                        <span>{{ $cat->icon }} {{ $cat->name }}</span>
                        <span class="text-xs text-gray-400">{{ $cat->documents_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Document list --}}
        <div class="flex-1 space-y-3">
            {{-- Search --}}
            <form method="GET" class="flex gap-2">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search documents..."
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200">Search</button>
            </form>

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif

            @forelse($documents as $doc)
            <div class="bg-white rounded-xl border p-4 hover:shadow-sm transition">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 text-xl">
                        {{ $doc->file_icon }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $doc->title }}</h3>
                                @if($doc->description)
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ $doc->description }}</p>
                                @endif
                            </div>
                            <a href="{{ route('documents.download', $doc) }}"
                                class="flex-shrink-0 flex items-center gap-1.5 text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                                ⬇ Download
                            </a>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 mt-2 text-xs text-gray-400">
                            @if($doc->category)
                            <span>{{ $doc->category->icon }} {{ $doc->category->name }}</span>
                            @endif
                            <span>{{ $doc->file_size_formatted }}</span>
                            @if($doc->version > 1)<span>v{{ $doc->version }}</span>@endif
                            <span>{{ $doc->download_count }} download{{ $doc->download_count !== 1 ? 's' : '' }}</span>
                            <span>{{ $doc->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border p-12 text-center">
                <div class="text-5xl mb-3">📁</div>
                <p class="text-gray-500 text-sm">
                    @if(request('search')) No documents match your search.
                    @elseif(request('category')) No documents in this category.
                    @else No documents published yet.
                    @endif
                </p>
            </div>
            @endforelse

            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection
