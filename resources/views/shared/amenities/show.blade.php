@extends('layouts.app')
@section('title', $amenity->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <a href="{{ route('amenities.index') }}" class="text-indigo-600 text-sm hover:underline">← Back to Amenities</a>

    {{-- Amenity Card --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        @if($amenity->photo_url)
        <div class="aspect-video w-full overflow-hidden bg-gray-100">
            <img src="{{ $amenity->photo_url }}" alt="{{ $amenity->name }}" class="w-full h-full object-cover">
        </div>
        @else
        <div class="aspect-video w-full bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center">
            <span class="text-6xl">{{ $amenity->type_icon }}</span>
        </div>
        @endif

        <div class="p-6">
            <div class="flex items-start justify-between gap-3 mb-3">
                <h1 class="text-2xl font-bold text-gray-800">{{ $amenity->name }}</h1>
                <span class="text-sm px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ ucfirst($amenity->type) }}</span>
            </div>

            @if($amenity->description)
            <p class="text-gray-600 text-sm mb-4">{{ $amenity->description }}</p>
            @endif

            <div class="grid grid-cols-2 gap-3 text-sm text-gray-600 border-t pt-4">
                <div><span class="font-medium text-gray-700">Capacity:</span> {{ $amenity->capacity }} concurrent</div>
                @if($amenity->opening_time)
                <div><span class="font-medium text-gray-700">Hours:</span> {{ $amenity->opening_time }} – {{ $amenity->closing_time }}</div>
                @endif
                <div><span class="font-medium text-gray-700">Fee:</span>
                    @if($amenity->is_free) Free @else Rs. {{ number_format($amenity->fee_per_hour, 0) }}/hour @endif
                </div>
                <div><span class="font-medium text-gray-700">Booking:</span>
                    {{ $amenity->requires_approval ? 'Requires approval' : 'Instant' }}
                </div>
            </div>

            <div class="mt-5">
                <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'book-amenity' }))"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Book This Space
                </button>
            </div>
        </div>
    </div>

    {{-- Upcoming bookings hint --}}
    @if($upcomingBookings->isNotEmpty())
    <div class="bg-white rounded-xl border p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Already Booked Slots</h2>
        <div class="space-y-2">
            @foreach($upcomingBookings->take(8) as $b)
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                <span>{{ $b->starts_at->format('d M Y, h:i A') }} – {{ $b->ends_at->format('h:i A') }}</span>
                <span class="text-xs px-1.5 py-0.5 rounded {{ $b->status_badge_class }}">{{ ucfirst($b->status) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Book Modal --}}
<x-modal name="book-amenity" :show="$errors->any()" maxWidth="lg">
    <div class="bg-white rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Book {{ $amenity->name }}</h2>
                @if(!$amenity->is_free)
                <p class="text-xs text-gray-500 mt-0.5">Rs. {{ number_format($amenity->fee_per_hour, 0) }}/hour · fee calculated at booking</p>
                @endif
            </div>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('amenities.book', $amenity) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                        min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('starts_at') border-red-400 @enderror">
                    @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('ends_at') border-red-400 @enderror">
                    @error('ends_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="text" name="purpose" value="{{ old('purpose') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="e.g. Birthday party, Meeting, Gym session">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Guests</label>
                <input type="number" name="guest_count" value="{{ old('guest_count', 0) }}" min="0"
                    class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            @if($amenity->requires_approval)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs px-3 py-2 rounded-lg">
                This amenity requires admin approval. You'll be notified once reviewed.
            </div>
            @endif
            <div class="flex gap-3 pt-2 border-t">
                <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    {{ $amenity->requires_approval ? 'Request Booking' : 'Confirm Booking' }}
                </button>
                <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
@endsection
