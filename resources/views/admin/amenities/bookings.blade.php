@extends('layouts.app')
@section('title', 'Amenity Bookings')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-bold text-gray-800">Amenity Bookings</h1>
        <a href="{{ route('admin.amenities.index') }}" class="text-indigo-600 text-sm hover:underline">← Manage Amenities</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border p-4 flex flex-wrap gap-3">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending','approved','rejected','cancelled','completed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="amenity" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
            <option value="">All Amenities</option>
            @foreach($amenities as $a)
            <option value="{{ $a->id }}" {{ request('amenity') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        <a href="{{ route('admin.amenity-bookings.index') }}" class="text-gray-500 text-sm py-1.5 hover:text-gray-700">Clear</a>
    </form>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Code</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Amenity</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Resident</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Slot</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Fee</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-indigo-600 font-bold">{{ $booking->booking_code }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $booking->amenity->name }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $booking->user->name }}
                        @if($booking->user->flat_number)
                        <br><span class="text-gray-400">Flat {{ $booking->user->block }}-{{ $booking->user->flat_number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $booking->starts_at->format('d M Y') }}<br>
                        {{ $booking->starts_at->format('h:i A') }} – {{ $booking->ends_at->format('h:i A') }}
                        <span class="text-gray-400">({{ $booking->duration_label }})</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $booking->total_fee > 0 ? 'Rs. ' . number_format($booking->total_fee, 2) : 'Free' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $booking->status_badge_class }}">{{ ucfirst($booking->status) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($booking->status === 'pending')
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.amenity-bookings.approve', $booking) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs text-green-600 hover:underline font-medium">Approve</button>
                            </form>
                            <button onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'reject-{{ $booking->id }}' }))"
                                class="text-xs text-red-500 hover:underline">Reject</button>
                        </div>
                        @endif
                    </td>
                </tr>

                {{-- Reject Modal --}}
                @if($booking->status === 'pending')
                <x-modal name="reject-{{ $booking->id }}" maxWidth="sm">
                    <div class="bg-white rounded-xl overflow-hidden">
                        <div class="px-6 py-5 space-y-3">
                            <h3 class="font-semibold text-gray-800">Reject Booking</h3>
                            <p class="text-sm text-gray-500">
                                <span class="font-mono text-indigo-600">{{ $booking->booking_code }}</span>
                                — {{ $booking->amenity->name }} by {{ $booking->user->name }}
                            </p>
                            <form action="{{ route('admin.amenity-bookings.reject', $booking) }}" method="POST" class="space-y-3">
                                @csrf @method('PATCH')
                                <textarea name="rejection_reason" rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Optional reason..."></textarea>
                                <div class="flex gap-3">
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Reject</button>
                                    <button type="button" @click="show = false" class="text-gray-500 text-sm py-2 hover:text-gray-700">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </x-modal>
                @endif

                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $bookings->links() }}
</div>
@endsection
