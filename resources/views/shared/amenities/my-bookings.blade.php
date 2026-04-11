@extends('layouts.app')
@section('title', 'My Bookings')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">My Amenity Bookings</h1>
        <a href="{{ route('amenities.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            Browse Amenities
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    @forelse($bookings as $booking)
    <div class="bg-white rounded-xl border p-5 hover:shadow-sm transition">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $booking->booking_code }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $booking->status_badge_class }}">{{ ucfirst($booking->status) }}</span>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $booking->amenity->name }}</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 mt-1 text-xs text-gray-400">
                    <span>📅 {{ $booking->starts_at->format('d M Y') }}</span>
                    <span>🕐 {{ $booking->starts_at->format('h:i A') }} – {{ $booking->ends_at->format('h:i A') }}</span>
                    <span>⏱ {{ $booking->duration_label }}</span>
                    @if($booking->total_fee > 0)<span>💰 Rs. {{ number_format($booking->total_fee, 2) }}</span>@endif
                    @if($booking->purpose)<span>📝 {{ $booking->purpose }}</span>@endif
                </div>
                @if($booking->rejection_reason)
                <p class="text-xs text-red-600 mt-1 bg-red-50 rounded px-2 py-1">Reason: {{ $booking->rejection_reason }}</p>
                @endif
            </div>
            @if($booking->isCancellable())
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'cancel-booking-{{ $booking->id }}' }))"
                class="flex-shrink-0 text-xs text-red-500 hover:underline font-medium">Cancel</button>
            @endif
        </div>
    </div>

    @if($booking->isCancellable())
    <x-modal name="cancel-booking-{{ $booking->id }}" maxWidth="sm">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Cancel Booking</h3>
                        <p class="text-sm text-gray-500 mt-0.5">This cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 border">
                    <span class="font-mono font-bold text-indigo-600">{{ $booking->booking_code }}</span>
                    — {{ $booking->amenity->name }} · {{ $booking->starts_at->format('d M, h:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-3 px-6 pb-5">
                <form action="{{ route('amenities.bookings.cancel', $booking) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Cancel Booking</button>
                </form>
                <button @click="show = false" class="text-gray-500 text-sm hover:text-gray-700">Keep</button>
            </div>
        </div>
    </x-modal>
    @endif

    @empty
    <div class="bg-white rounded-xl border p-12 text-center">
        <div class="text-5xl mb-3">📅</div>
        <p class="text-gray-500 text-sm">No bookings yet.</p>
        <a href="{{ route('amenities.index') }}" class="mt-3 inline-block text-indigo-600 text-sm hover:underline">Browse amenities to book</a>
    </div>
    @endforelse

    {{ $bookings->links() }}
</div>
@endsection
