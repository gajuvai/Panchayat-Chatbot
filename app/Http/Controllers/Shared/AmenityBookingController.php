<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AmenityBookingController extends Controller
{
    public function store(Request $request, Amenity $amenity): RedirectResponse
    {
        abort_unless($amenity->is_active, 404);

        $data = $request->validate([
            'starts_at'   => ['required', 'date', 'after:now'],
            'ends_at'     => ['required', 'date', 'after:starts_at'],
            'purpose'     => ['nullable', 'string', 'max:255'],
            'guest_count' => ['integer', 'min:0', 'max:100'],
        ]);

        $start = now()->parse($data['starts_at']);
        $end   = now()->parse($data['ends_at']);

        // Conflict check
        if (!$amenity->isAvailableAt($start, $end)) {
            return back()->withErrors([
                'starts_at' => 'This time slot is already fully booked. Please choose a different time.',
            ])->withInput();
        }

        $minutes   = (int) $start->diffInMinutes($end);
        $totalFee  = $amenity->calculateFee($minutes);
        $status    = $amenity->requires_approval ? 'pending' : 'approved';

        AmenityBooking::create([
            'amenity_id'  => $amenity->id,
            'user_id'     => $request->user()->id,
            'starts_at'   => $start,
            'ends_at'     => $end,
            'purpose'     => $data['purpose'] ?? null,
            'guest_count' => $data['guest_count'] ?? 0,
            'status'      => $status,
            'total_fee'   => $totalFee,
        ]);

        $msg = $amenity->requires_approval
            ? 'Booking request submitted. Awaiting admin approval.'
            : 'Booking confirmed!';

        return redirect()->route('amenities.my-bookings')
            ->with('success', $msg);
    }

    public function myBookings(Request $request): \Illuminate\View\View
    {
        $bookings = AmenityBooking::with('amenity')
            ->where('user_id', $request->user()->id)
            ->latest('starts_at')
            ->paginate(10);

        return view('shared.amenities.my-bookings', compact('bookings'));
    }

    public function destroy(Request $request, AmenityBooking $amenityBooking): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $amenityBooking->user_id || $request->user()->isAdmin(),
            403
        );
        abort_unless($amenityBooking->isCancellable(), 422, 'This booking cannot be cancelled.');

        $amenityBooking->update(['status' => 'cancelled']);

        return redirect()->route('amenities.my-bookings')
            ->with('success', 'Booking cancelled.');
    }
}
