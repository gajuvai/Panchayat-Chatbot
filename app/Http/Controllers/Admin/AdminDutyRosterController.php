<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DutyAssignment;
use App\Models\DutyRoster;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDutyRosterController extends Controller
{
    public function index(Request $request): View
    {
        $query = DutyRoster::with(['createdBy', 'assignments.user'])
            ->withCount('assignments')
            ->orderBy('roster_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $tab = $request->get('tab', 'upcoming');
        if ($tab === 'past') {
            $query->where('roster_date', '<', today());
        } else {
            $query->where('roster_date', '>=', today());
        }

        $rosters = $query->paginate(15)->withQueryString();
        $residents = User::whereHas('role', fn ($q) => $q->where('name', 'resident'))
            ->orderBy('name')->get();

        return view('admin.duty-roster.index', compact('rosters', 'residents', 'tab'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'type'           => ['required', 'in:weekly_duty,event_volunteer,committee,other'],
            'roster_date'    => ['required', 'date'],
            'shift_start'    => ['required', 'date_format:H:i'],
            'shift_end'      => ['required', 'date_format:H:i', 'after:shift_start'],
            'slots_required' => ['required', 'integer', 'min:1'],
            'is_open_signup' => ['boolean'],
        ]);

        $data['created_by']     = $request->user()->id;
        $data['is_open_signup'] = $request->boolean('is_open_signup');

        DutyRoster::create($data);

        return redirect()->route('admin.duty-roster.index')
            ->with('success', 'Duty roster created.');
    }

    public function update(Request $request, DutyRoster $dutyRoster): RedirectResponse
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'type'           => ['required', 'in:weekly_duty,event_volunteer,committee,other'],
            'roster_date'    => ['required', 'date'],
            'shift_start'    => ['required', 'date_format:H:i'],
            'shift_end'      => ['required', 'date_format:H:i'],
            'slots_required' => ['required', 'integer', 'min:1'],
            'is_open_signup' => ['boolean'],
        ]);

        $data['is_open_signup'] = $request->boolean('is_open_signup');
        $dutyRoster->update($data);

        return redirect()->route('admin.duty-roster.index')
            ->with('success', 'Roster updated.');
    }

    public function destroy(DutyRoster $dutyRoster): RedirectResponse
    {
        $dutyRoster->delete();
        return redirect()->route('admin.duty-roster.index')
            ->with('success', 'Roster deleted.');
    }

    public function assign(Request $request, DutyRoster $dutyRoster): RedirectResponse
    {
        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'notes'      => ['nullable', 'string', 'max:300'],
        ]);

        $assigned = 0;
        foreach ($data['user_ids'] as $userId) {
            // Skip if already assigned
            if (!$dutyRoster->isUserAssigned((int) $userId)) {
                DutyAssignment::create([
                    'roster_id'    => $dutyRoster->id,
                    'user_id'      => $userId,
                    'status'       => 'assigned',
                    'notes'        => $data['notes'] ?? null,
                    'is_voluntary' => false,
                ]);
                $assigned++;
            }
        }

        return redirect()->route('admin.duty-roster.index')
            ->with('success', "{$assigned} resident(s) assigned to \"{$dutyRoster->title}\".");
    }

    public function removeAssignment(DutyAssignment $assignment): RedirectResponse
    {
        $assignment->delete();
        return back()->with('success', 'Assignment removed.');
    }
}
