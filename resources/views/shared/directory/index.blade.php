@extends('layouts.app')
@section('title', 'Community Directory')

@section('content')
<div class="space-y-5">

    {{-- My Listing (residents only) --}}
    @if(auth()->user()->isResident())
    @php $me = auth()->user(); @endphp
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">My Directory Listing</h2>
                <p class="text-xs text-gray-500 mt-0.5">Control how you appear to other residents.</p>
            </div>
            @if($me->is_listed_in_directory)
                <span class="text-xs px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-medium">Listed</span>
            @else
                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Not Listed</span>
            @endif
        </div>

        <form action="{{ route('directory.update') }}" method="POST" class="p-6" x-data="{ listed: {{ $me->is_listed_in_directory ? 'true' : 'false' }} }">
            @csrf @method('PATCH')

            {{-- Toggle --}}
            <div class="flex items-start gap-4 mb-5">
                <div class="flex items-center mt-0.5">
                    <input type="hidden" name="is_listed_in_directory" value="0">
                    <input type="checkbox" name="is_listed_in_directory" id="listed_toggle" value="1"
                        x-model="listed"
                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ $me->is_listed_in_directory ? 'checked' : '' }}>
                </div>
                <label for="listed_toggle" class="cursor-pointer">
                    <p class="text-sm font-medium text-gray-800">List me in the Community Directory</p>
                    <p class="text-xs text-gray-500 mt-0.5">Other residents can see your name, flat, and any info you share below.</p>
                </label>
            </div>

            {{-- Directory fields (shown when listed) --}}
            <div x-show="listed" x-transition class="space-y-4 border-t pt-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="directory_display_name"
                            value="{{ old('directory_display_name', $me->directory_display_name) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="{{ $me->name }} (default)">
                        <p class="text-xs text-gray-400 mt-1">Leave blank to use your account name.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="whatsapp"
                            value="{{ old('whatsapp', $me->whatsapp) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="+977-98XXXXXXXX">
                        <p class="text-xs text-gray-400 mt-1">Shown as a link to other residents.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio <span class="text-gray-400 font-normal">(optional, max 300 chars)</span></label>
                    <textarea name="bio" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="A short note about yourself..."
                        maxlength="300">{{ old('bio', $me->bio) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interests <span class="text-gray-400 font-normal">(comma-separated)</span></label>
                    <input type="text" name="interests"
                        value="{{ old('interests', $me->interests ? implode(', ', $me->interests) : '') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Gardening, Yoga, Photography">
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Directory --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Community Directory</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $residents->total() }} resident(s) listed</p>
            </div>
        </div>

        {{-- Search + Filter --}}
        <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name or flat..."
                class="flex-1 min-w-40 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">

            @if($blocks->isNotEmpty())
            <select name="block" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                <option value="{{ $block }}" {{ request('block') === $block ? 'selected' : '' }}>Block {{ $block }}</option>
                @endforeach
            </select>
            @endif

            <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700 transition">
                Search
            </button>
            @if(request('search') || request('block'))
            <a href="{{ route('directory.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
            @endif
        </form>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif

        {{-- Resident Cards --}}
        @if($residents->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($residents as $resident)
            <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
                {{-- Avatar + Name --}}
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg flex-shrink-0">
                        {{ strtoupper(substr($resident->directory_display_name ?: $resident->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 truncate">
                            {{ $resident->directory_display_name ?: $resident->name }}
                        </p>
                        @if($resident->block || $resident->flat_number)
                        <p class="text-xs text-gray-500">
                            Flat {{ $resident->block ? $resident->block . '-' : '' }}{{ $resident->flat_number ?? '—' }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Bio --}}
                @if($resident->bio)
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $resident->bio }}</p>
                @endif

                {{-- Interests --}}
                @if($resident->interests && count($resident->interests))
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach(array_slice($resident->interests, 0, 4) as $interest)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ $interest }}
                    </span>
                    @endforeach
                    @if(count($resident->interests) > 4)
                    <span class="text-xs text-gray-400">+{{ count($resident->interests) - 4 }} more</span>
                    @endif
                </div>
                @endif

                {{-- Contact --}}
                @if($resident->whatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $resident->whatsapp) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 text-xs text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
                @endif
            </div>
            @endforeach
        </div>

        {{ $residents->links() }}

        @else
        <div class="bg-white rounded-xl border p-12 text-center">
            <div class="text-5xl mb-3">👥</div>
            @if(request('search') || request('block'))
            <p class="text-gray-500 text-sm">No residents found matching your search.</p>
            <a href="{{ route('directory.index') }}" class="mt-2 inline-block text-indigo-600 text-sm hover:underline">Clear filters</a>
            @else
            <p class="text-gray-500 text-sm">No residents have joined the directory yet.</p>
            @if(auth()->user()->isResident())
            <p class="text-gray-400 text-xs mt-1">Be the first — enable your listing above!</p>
            @endif
            @endif
        </div>
        @endif
    </div>

</div>
@endsection
