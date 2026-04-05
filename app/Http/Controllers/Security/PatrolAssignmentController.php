<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\PatrolAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatrolAssignmentController extends Controller
{
    /**
     * Display a listing of patrol assignments.
     */
    public function index(): View
    {
        $patrols = PatrolAssignment::with(['assignedTo', 'assignedBy'])
            ->latest('shift_start')
            ->paginate(10);

        return view('security.patrol.index', compact('patrols'));
    }

    /**
     * Show the form for creating a new patrol assignment.
     */
    public function create(): View
    {
        $officers = User::whereHas('role', fn ($q) => $q->where('name', 'security_head'))
            ->orderBy('name')
            ->get();

        return view('security.patrol.create', compact('officers'));
    }

    /**
     * Store a newly created patrol assignment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'area'        => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'shift_start' => ['required', 'date'],
            'shift_end'   => ['required', 'date', 'after:shift_start'],
            'notes'       => ['nullable', 'string'],
            'status'      => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        $data['assigned_by'] = $request->user()->id;

        PatrolAssignment::create($data);

        return redirect()->route('security.patrols.index')
            ->with('success', 'Patrol assignment created successfully.');
    }

    /**
     * Display the specified patrol assignment.
     */
    public function show(PatrolAssignment $patrol): View
    {
        $patrol->load(['assignedTo', 'assignedBy']);

        return view('security.patrol.show', compact('patrol'));
    }

    /**
     * Show the form for editing the specified patrol assignment.
     */
    public function edit(PatrolAssignment $patrol): View
    {
        $officers = User::whereHas('role', fn ($q) => $q->where('name', 'security_head'))
            ->orderBy('name')
            ->get();

        return view('security.patrol.edit', compact('patrol', 'officers'));
    }

    /**
     * Update the specified patrol assignment in storage.
     */
    public function update(Request $request, PatrolAssignment $patrol): RedirectResponse
    {
        $data = $request->validate([
            'area'        => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'shift_start' => ['required', 'date'],
            'shift_end'   => ['required', 'date', 'after:shift_start'],
            'notes'       => ['nullable', 'string'],
            'status'      => ['required', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        $patrol->update($data);

        return redirect()->route('security.patrols.show', $patrol)
            ->with('success', 'Patrol assignment updated successfully.');
    }

    /**
     * Remove the specified patrol assignment from storage.
     */
    public function destroy(PatrolAssignment $patrol): RedirectResponse
    {
        $patrol->delete();

        return redirect()->route('security.patrols.index')
            ->with('success', 'Patrol assignment deleted.');
    }
}
