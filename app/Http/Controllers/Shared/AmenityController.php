<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(): View
    {
        $amenities = Amenity::active()->orderBy('type')->orderBy('name')->get();

        return view('shared.amenities.index', compact('amenities'));
    }

    public function show(Amenity $amenity): View
    {
        abort_unless($amenity->is_active, 404);

        // Upcoming bookings for this amenity (for the calendar hint)
        $upcomingBookings = $amenity->bookings()
            ->with('user')
            ->whereIn('status', ['approved', 'pending'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(20)
            ->get();

        return view('shared.amenities.show', compact('amenity', 'upcomingBookings'));
    }
}
