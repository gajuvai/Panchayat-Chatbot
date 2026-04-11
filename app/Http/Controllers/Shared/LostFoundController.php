<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\LostAndFoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LostFoundController extends Controller
{
    public function index(Request $request): View
    {
        $query = LostAndFoundItem::with('poster')->latest();

        // Tab filter: lost / found / resolved
        $tab = $request->get('tab', 'active');
        if ($tab === 'resolved') {
            $query->where('is_resolved', true);
        } elseif ($tab === 'lost') {
            $query->active()->lost();
        } elseif ($tab === 'found') {
            $query->active()->found();
        } else {
            $query->active(); // default: all active
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                                       ->orWhere('description', 'like', "%{$s}%")
                                       ->orWhere('location', 'like', "%{$s}%"));
        }

        $items = $query->paginate(12)->withQueryString();

        // Counts for tab badges
        $counts = [
            'active'   => LostAndFoundItem::active()->count(),
            'lost'     => LostAndFoundItem::active()->lost()->count(),
            'found'    => LostAndFoundItem::active()->found()->count(),
            'resolved' => LostAndFoundItem::where('is_resolved', true)->count(),
        ];

        return view('shared.lost-found.index', compact('items', 'tab', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'          => ['required', 'in:lost,found'],
            'title'         => ['required', 'string', 'max:100'],
            'description'   => ['required', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'date_occurred' => ['required', 'date', 'before_or_equal:today'],
            'contact_info'  => ['nullable', 'string', 'max:255'],
            'photo'         => ['nullable', 'image', 'max:5120'], // 5MB
        ]);

        $data['user_id'] = $request->user()->id;

        $item = LostAndFoundItem::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store("lost-found/{$item->id}", 'public');
            $item->update(['photo_path' => $path]);
        }

        return redirect()->route('lost-found.index')
            ->with('success', ucfirst($data['type']) . ' item reported: "' . $item->title . '"');
    }

    public function update(Request $request, LostAndFoundItem $lostFound): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $lostFound->user_id || $request->user()->isAdmin(),
            403
        );

        $data = $request->validate([
            'type'          => ['required', 'in:lost,found'],
            'title'         => ['required', 'string', 'max:100'],
            'description'   => ['required', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'date_occurred' => ['required', 'date', 'before_or_equal:today'],
            'contact_info'  => ['nullable', 'string', 'max:255'],
            'photo'         => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($lostFound->photo_path) {
                Storage::disk('public')->delete($lostFound->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store("lost-found/{$lostFound->id}", 'public');
        }

        unset($data['photo']);
        $lostFound->update($data);

        return redirect()->route('lost-found.index')
            ->with('success', 'Item updated successfully.');
    }

    public function resolve(Request $request, LostAndFoundItem $lostFound): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $lostFound->user_id || $request->user()->isAdmin(),
            403
        );

        $lostFound->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        return redirect()->route('lost-found.index')
            ->with('success', '"' . $lostFound->title . '" marked as resolved.');
    }

    public function destroy(Request $request, LostAndFoundItem $lostFound): RedirectResponse
    {
        abort_unless(
            $request->user()->id === $lostFound->user_id || $request->user()->isAdmin(),
            403
        );

        if ($lostFound->photo_path) {
            Storage::disk('public')->delete($lostFound->photo_path);
        }

        $lostFound->delete();

        return redirect()->route('lost-found.index')
            ->with('success', 'Item removed.');
    }
}
