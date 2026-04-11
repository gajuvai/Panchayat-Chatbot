<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAmenityController extends Controller
{
    // ─── Amenity Management ───────────────────────────────────────────────────

    public function index(): View
    {
        $amenities = Amenity::withCount(['bookings as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->orderBy('type')->orderBy('name')->get();

        $pendingTotal = AmenityBooking::where('status', 'pending')->count();

        return view('admin.amenities.index', compact('amenities', 'pendingTotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'type'              => ['required', 'in:parking,hall,gym,pool,garden,other'],
            'description'       => ['nullable', 'string'],
            'capacity'          => ['required', 'integer', 'min:1'],
            'requires_approval' => ['boolean'],
            'fee_per_hour'      => ['numeric', 'min:0'],
            'opening_time'      => ['nullable', 'date_format:H:i'],
            'closing_time'      => ['nullable', 'date_format:H:i'],
            'available_days'    => ['nullable', 'array'],
            'available_days.*'  => ['integer', 'between:0,6'],
            'photo'             => ['nullable', 'image', 'max:5120'],
        ]);

        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['fee_per_hour']      = $request->input('fee_per_hour', 0);
        $data['available_days']    = $request->input('available_days');

        $amenity = Amenity::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store("amenities/{$amenity->id}", 'public');
            $amenity->update(['photo_path' => $path]);
        }

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity \"{$amenity->name}\" created.");
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'type'              => ['required', 'in:parking,hall,gym,pool,garden,other'],
            'description'       => ['nullable', 'string'],
            'capacity'          => ['required', 'integer', 'min:1'],
            'requires_approval' => ['boolean'],
            'fee_per_hour'      => ['numeric', 'min:0'],
            'opening_time'      => ['nullable', 'date_format:H:i'],
            'closing_time'      => ['nullable', 'date_format:H:i'],
            'available_days'    => ['nullable', 'array'],
            'available_days.*'  => ['integer', 'between:0,6'],
            'photo'             => ['nullable', 'image', 'max:5120'],
            'is_active'         => ['boolean'],
        ]);

        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_active']         = $request->boolean('is_active');
        $data['available_days']    = $request->input('available_days');

        if ($request->hasFile('photo')) {
            if ($amenity->photo_path) Storage::disk('public')->delete($amenity->photo_path);
            $data['photo_path'] = $request->file('photo')->store("amenities/{$amenity->id}", 'public');
        }

        unset($data['photo']);
        $amenity->update($data);

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity updated.");
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        if ($amenity->photo_path) Storage::disk('public')->delete($amenity->photo_path);
        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Amenity deleted.");
    }

    // ─── Booking Management ───────────────────────────────────────────────────

    public function bookings(Request $request): View
    {
        $query = AmenityBooking::with(['amenity', 'user'])->latest('starts_at');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('amenity')) $query->where('amenity_id', $request->amenity);

        $bookings  = $query->paginate(15)->withQueryString();
        $amenities = Amenity::orderBy('name')->get();

        return view('admin.amenities.bookings', compact('bookings', 'amenities'));
    }

    public function approveBooking(Request $request, AmenityBooking $booking): RedirectResponse
    {
        abort_unless($booking->status === 'pending', 422, 'Booking is not pending.');

        $booking->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
        ]);

        return back()->with('success', "Booking {$booking->booking_code} approved.");
    }

    public function rejectBooking(Request $request, AmenityBooking $booking): RedirectResponse
    {
        abort_unless($booking->status === 'pending', 422, 'Booking is not pending.');

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status'           => 'rejected',
            'reviewed_by'      => $request->user()->id,
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return back()->with('success', "Booking {$booking->booking_code} rejected.");
    }
}
