@extends('layouts.app')
@section('title', 'Book Amenities')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Community Amenities</h1>
            <p class="text-sm text-gray-500 mt-0.5">Book shared spaces for your use.</p>
        </div>
        <a href="{{ route('amenities.my-bookings') }}"
            class="border border-indigo-300 text-indigo-600 px-4 py-2 rounded-lg text-sm hover:bg-indigo-50 transition">
            My Bookings
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    @if($amenities->isEmpty())
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">🏢</div>
        <p class="text-gray-500 text-sm">No amenities available yet.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($amenities as $amenity)
        <div class="bg-white rounded-xl border overflow-hidden hover:shadow-sm transition flex flex-col">

            {{-- Photo / placeholder --}}
            @if($amenity->photo_url)
            <div class="aspect-video w-full overflow-hidden bg-gray-100">
                <img src="{{ $amenity->photo_url }}" alt="{{ $amenity->name }}"
                    class="w-full h-full object-cover">
            </div>
            @else
            <div class="aspect-video w-full bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center">
                <span class="text-5xl">{{ $amenity->type_icon }}</span>
            </div>
            @endif

            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h3 class="font-semibold text-gray-800 text-base">{{ $amenity->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 flex-shrink-0">
                        {{ ucfirst($amenity->type) }}
                    </span>
                </div>

                @if($amenity->description)
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $amenity->description }}</p>
                @endif

                <div class="space-y-1 text-xs text-gray-400 mb-4">
                    @if($amenity->capacity > 1)
                    <p>👥 Up to {{ $amenity->capacity }} concurrent booking(s)</p>
                    @endif
                    @if($amenity->opening_time)
                    <p>🕐 {{ $amenity->opening_time }} – {{ $amenity->closing_time ?? '—' }}</p>
                    @endif
                    @if($amenity->is_free)
                    <p>💰 Free to use</p>
                    @else
                    <p>💰 Rs. {{ number_format($amenity->fee_per_hour, 0) }}/hour</p>
                    @endif
                    @if($amenity->requires_approval)
                    <p>✅ Requires approval</p>
                    @else
                    <p>✅ Instant booking</p>
                    @endif
                </div>

                <div class="mt-auto">
                    <a href="{{ route('amenities.show', $amenity) }}"
                        class="block w-full bg-indigo-600 text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                        View & Book
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
