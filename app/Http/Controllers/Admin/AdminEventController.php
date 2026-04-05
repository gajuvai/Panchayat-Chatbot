<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    public function index(): View
    {
        $events = Event::withCount(['rsvps' => fn($q) => $q->where('status', 'attending')])
            ->with('organizer')
            ->latest('event_date')
            ->paginate(10);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'venue'         => ['required', 'string', 'max:255'],
            'event_date'    => ['required', 'date', 'after:now'],
            'end_date'      => ['required', 'date', 'after:event_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status']  = 'upcoming';

        Event::create($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $event->load(['organizer', 'rsvps.user']);
        $rsvpCount = $event->rsvps->where('status', 'attending')->count();

        return view('admin.events.show', compact('event', 'rsvpCount'));
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'venue'         => ['required', 'string', 'max:255'],
            'event_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after:event_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status'        => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ]);

        $event->update($data);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }
}
