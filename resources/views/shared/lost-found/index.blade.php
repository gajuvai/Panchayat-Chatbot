@extends('layouts.app')
@section('title', 'Lost & Found')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Lost & Found Board</h1>
            <p class="text-sm text-gray-500 mt-0.5">Help reunite items with their owners.</p>
        </div>
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'report-item' }))"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Report Item
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 bg-white rounded-xl border p-1 w-fit flex-wrap">
        @foreach(['active' => 'All Active', 'lost' => 'Lost', 'found' => 'Found', 'resolved' => 'Resolved'] as $key => $label)
        <a href="{{ route('lost-found.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                {{ $tab === $key
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
            {{ $label }}
            <span class="ml-1 text-xs opacity-70">({{ $counts[$key] }})</span>
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search title, description, location..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-1.5 rounded-lg text-sm hover:bg-gray-200 transition">Search</button>
        @if(request('search'))
        <a href="{{ route('lost-found.index', ['tab' => $tab]) }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
        @endif
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Item Grid --}}
    @if($items->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $item)
        <div class="bg-white rounded-xl border overflow-hidden hover:shadow-sm transition flex flex-col">

            {{-- Photo --}}
            @if($item->photo_url)
            <div class="aspect-video w-full overflow-hidden bg-gray-100">
                <img src="{{ $item->photo_url }}" alt="{{ $item->title }}"
                    class="w-full h-full object-cover">
            </div>
            @else
            <div class="aspect-video w-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                <span class="text-4xl">{{ $item->type === 'lost' ? '🔍' : '📦' }}</span>
            </div>
            @endif

            <div class="p-4 flex-1 flex flex-col">
                {{-- Type + Resolved badge --}}
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $item->type_badge_class }}">
                        {{ $item->type_label }}
                    </span>
                    @if($item->is_resolved)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Resolved</span>
                    @endif
                </div>

                <h3 class="font-semibold text-gray-800 mb-1">{{ $item->title }}</h3>
                <p class="text-sm text-gray-500 line-clamp-2 mb-3 flex-1">{{ $item->description }}</p>

                <div class="space-y-1 text-xs text-gray-400 mb-3">
                    @if($item->location)
                    <p>📍 {{ $item->location }}</p>
                    @endif
                    <p>📅 {{ $item->date_occurred->format('d M Y') }}</p>
                    <p>Posted by {{ $item->poster->name }}
                        @if($item->poster->flat_number)
                        · Flat {{ $item->poster->block }}-{{ $item->poster->flat_number }}
                        @endif
                    </p>
                    @if($item->contact_info)
                    <p>📞 {{ $item->contact_info }}</p>
                    @endif
                </div>

                {{-- Actions (owner or admin) --}}
                @if(!$item->is_resolved && (auth()->id() === $item->user_id || auth()->user()->isAdmin()))
                <div class="flex items-center gap-2 pt-3 border-t mt-auto">
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-item-{{ $item->id }}' }))"
                        class="text-xs text-indigo-600 hover:underline font-medium">Edit</button>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'resolve-item-{{ $item->id }}' }))"
                        class="text-xs text-green-600 hover:underline font-medium">Mark Resolved</button>
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-item-{{ $item->id }}' }))"
                        class="text-xs text-red-400 hover:underline ml-auto">Delete</button>
                </div>
                @elseif($item->is_resolved && auth()->user()->isAdmin())
                <div class="flex items-center gap-2 pt-3 border-t mt-auto">
                    <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-item-{{ $item->id }}' }))"
                        class="text-xs text-red-400 hover:underline ml-auto">Delete</button>
                </div>
                @endif
            </div>
        </div>

        {{-- Edit Modal --}}
        @if(auth()->id() === $item->user_id || auth()->user()->isAdmin())
        <x-modal name="edit-item-{{ $item->id }}" :show="old('_edit_id') == $item->id && $errors->any()" maxWidth="xl">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-base font-semibold text-gray-800">Edit Item</h2>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto max-h-[80vh]">
                <form action="{{ route('lost-found.update', $item) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PATCH')
                    <input type="hidden" name="_edit_id" value="{{ $item->id }}">
                    @php $isActive = old('_edit_id') == $item->id && $errors->any(); @endphp

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="lost"  {{ ($isActive ? old('type') : $item->type) === 'lost'  ? 'selected' : '' }}>Lost</option>
                                <option value="found" {{ ($isActive ? old('type') : $item->type) === 'found' ? 'selected' : '' }}>Found</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="date_occurred"
                                value="{{ $isActive ? old('date_occurred') : $item->date_occurred->format('Y-m-d') }}"
                                max="{{ today()->format('Y-m-d') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date_occurred') border-red-400 @enderror">
                            @error('date_occurred')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ $isActive ? old('title') : $item->title }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror">{{ $isActive ? old('description') : $item->description }}</textarea>
                        @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                            <input type="text" name="location" value="{{ $isActive ? old('location') : $item->location }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Where found / last seen">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Info</label>
                            <input type="text" name="contact_info" value="{{ $isActive ? old('contact_info') : $item->contact_info }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Phone or note">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Replace Photo <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="file" name="photo" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @if($item->photo_url)
                        <p class="text-xs text-gray-400 mt-1">Current photo will be kept unless you upload a new one.</p>
                        @endif
                    </div>
                    <div class="flex gap-3 pt-2 border-t">
                        <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Save Changes</button>
                        <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                    </div>
                </form>
                </div>
            </div>
        </x-modal>

        {{-- Resolve Modal --}}
        @if(!$item->is_resolved)
        <x-modal name="resolve-item-{{ $item->id }}" maxWidth="sm">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Mark as Resolved</h3>
                            <p class="text-sm text-gray-500 mt-0.5">The item has been found or claimed?</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $item->title }}</p>
                </div>
                <div class="flex items-center gap-3 px-6 pb-5">
                    <form action="{{ route('lost-found.resolve', $item) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">Mark Resolved</button>
                    </form>
                    <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                </div>
            </div>
        </x-modal>
        @endif

        {{-- Delete Modal --}}
        <x-modal name="delete-item-{{ $item->id }}" maxWidth="sm">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">Remove Item</h3>
                            <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border font-medium">{{ $item->title }}</p>
                </div>
                <div class="flex items-center gap-3 px-6 pb-5">
                    <form action="{{ route('lost-found.destroy', $item) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Remove</button>
                    </form>
                    <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Cancel</button>
                </div>
            </div>
        </x-modal>
        @endif

        @endforeach
    </div>

    {{ $items->links() }}

    @else
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">{{ $tab === 'found' ? '📦' : '🔍' }}</div>
        <p class="text-gray-500 text-sm">
            @if(request('search'))
                No items match your search.
            @elseif($tab === 'resolved')
                No resolved items yet.
            @elseif($tab === 'lost')
                No lost items reported.
            @elseif($tab === 'found')
                No found items posted.
            @else
                Nothing posted yet. Be the first to help the community!
            @endif
        </p>
        @if(!request('search'))
        <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'report-item' }))"
            class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Report an item</button>
        @endif
    </div>
    @endif

</div>

{{-- Create / Report Item Modal --}}
<x-modal name="report-item" :show="$errors->any() && !old('_edit_id')" maxWidth="xl">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Report a Lost or Found Item</h2>
                <p class="text-xs text-gray-500 mt-0.5">Help the community by posting what you lost or found.</p>
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto max-h-[80vh]">
        <form action="{{ route('lost-found.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            {{-- Type toggle --}}
            <div x-data="{ type: '{{ old('type', 'lost') }}' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Item Type <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="lost" x-model="type" class="sr-only">
                        <div :class="type === 'lost' ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            class="border-2 rounded-lg p-3 text-center text-sm font-medium transition">
                            🔍 I Lost Something
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="type" value="found" x-model="type" class="sr-only">
                        <div :class="type === 'found' ? 'border-green-400 bg-green-50 text-green-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                            class="border-2 rounded-lg p-3 text-center text-sm font-medium transition">
                            📦 I Found Something
                        </div>
                    </label>
                </div>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
                    placeholder="e.g. Black wallet, Keys with red keychain, Golden ring">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
                    placeholder="Describe the item clearly — colour, size, brand, any markings...">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="location" value="{{ old('location') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="e.g. Lift lobby, Gate B, Parking">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="date_occurred"
                        value="{{ old('date_occurred', today()->format('Y-m-d')) }}"
                        max="{{ today()->format('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date_occurred') border-red-400 @enderror">
                    @error('date_occurred')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Info <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="contact_info" value="{{ old('contact_info') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Phone, WhatsApp, or flat number">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo <span class="text-gray-400 font-normal">(optional, max 5MB)</span></label>
                <input type="file" name="photo" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Post Item
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
        </div>
    </div>
</x-modal>
@endsection
