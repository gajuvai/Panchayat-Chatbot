<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityIncidentController extends Controller
{
    public function index(): View
    {
        $incidents = SecurityIncident::with('reporter')->latest()->paginate(15);
        return view('security.incidents.index', compact('incidents'));
    }

    public function create(): View
    {
        return view('security.incidents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'incident_type' => ['required', 'in:theft,trespass,vandalism,suspicious_activity,emergency,other'],
            'description'   => ['required', 'string'],
            'location'      => ['required', 'string', 'max:255'],
            'occurred_at'   => ['required', 'date'],
            'severity'      => ['required', 'in:low,medium,high,critical'],
        ]);

        $data['reported_by'] = $request->user()->id;
        SecurityIncident::create($data);

        return redirect()->route('security.incidents.index')
            ->with('success', 'Incident reported successfully.');
    }

    public function show(SecurityIncident $incident): View
    {
        return view('security.incidents.show', compact('incident'));
    }

    public function edit(SecurityIncident $incident): View
    {
        return view('security.incidents.edit', compact('incident'));
    }

    public function update(Request $request, SecurityIncident $incident): RedirectResponse
    {
        $data = $request->validate([
            'status'   => ['required', 'in:active,investigating,resolved'],
            'severity' => ['required', 'in:low,medium,high,critical'],
        ]);

        $incident->update($data);
        return back()->with('success', 'Incident updated.');
    }

    public function destroy(SecurityIncident $incident): RedirectResponse
    {
        $incident->delete();
        return redirect()->route('security.incidents.index')->with('success', 'Incident removed.');
    }
}
