<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\DutyAssignment;
use App\Models\DutyRoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DutyRosterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // User's upcoming assignments
        $myAssignments = DutyAssignment::with('roster')
            ->where('user_id', $user->id)
            ->whereHas('roster', fn ($q) => $q->where('roster_date', '>=', today()))
            ->whereIn('status', ['assigned', 'confirmed'])
            ->orderBy('created_at')
            ->get()
            ->sortBy('roster.roster_date');

        // Open rosters the user can self-signup for
        $openRosters = DutyRoster::with(['assignments' => fn ($q) => $q->whereIn('status', ['assigned', 'confirmed'])])
            ->upcoming()
            ->openSignup()
            ->whereDoesntHave('assignments', fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->filter(fn ($r) => !$r->isFull());

        // Past completed duties
        $pastDuties = DutyAssignment::with('roster')
            ->where('user_id', $user->id)
            ->whereHas('roster', fn ($q) => $q->where('roster_date', '<', today()))
            ->latest()
            ->limit(10)
            ->get();

        return view('shared.duty-roster.index', compact('myAssignments', 'openRosters', 'pastDuties'));
    }

    public function signup(Request $request, DutyRoster $dutyRoster): RedirectResponse
    {
        abort_unless($dutyRoster->is_open_signup, 403, 'This roster is not open for self-signup.');
        abort_if($dutyRoster->isFull(), 422, 'This roster is already full.');
        abort_if($dutyRoster->isUserAssigned($request->user()->id), 422, 'You are already signed up.');
        abort_if($dutyRoster->roster_date->isPast(), 422, 'This roster date has passed.');

        DutyAssignment::create([
            'roster_id'    => $dutyRoster->id,
            'user_id'      => $request->user()->id,
            'status'       => 'confirmed',
            'is_voluntary' => true,
        ]);

        return redirect()->route('duty-roster.index')
            ->with('success', "Signed up for \"{$dutyRoster->title}\"!");
    }

    public function confirm(DutyAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->user_id === auth()->id(), 403);
        abort_unless($assignment->status === 'assigned', 422);

        $assignment->update(['status' => 'confirmed']);

        return redirect()->route('duty-roster.index')
            ->with('success', 'Duty confirmed.');
    }

    public function decline(DutyAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->user_id === auth()->id(), 403);
        abort_unless(in_array($assignment->status, ['assigned', 'confirmed']), 422);

        $assignment->update(['status' => 'declined']);

        return redirect()->route('duty-roster.index')
            ->with('success', 'Duty declined.');
    }
}
