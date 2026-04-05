<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', '!=', 'cancelled')
            ->orderBy('event_date')->paginate(12);
        return view('shared.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['organizer', 'rsvps.user']);
        $userRsvp = $event->rsvps()->where('user_id', auth()->id())->first();
        return view('shared.events.show', compact('event', 'userRsvp'));
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'status'       => ['required', 'in:attending,not_attending,maybe'],
            'guests_count' => ['integer', 'min:0', 'max:10'],
            'notes'        => ['nullable', 'string', 'max:255'],
        ]);

        EventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            $data
        );

        return back()->with('success', 'RSVP updated successfully.');
    }
}
