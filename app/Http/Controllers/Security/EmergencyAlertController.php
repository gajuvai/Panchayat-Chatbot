<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyAlertController extends Controller
{
    public function index(): View
    {
        $alerts = EmergencyAlert::with(['triggeredBy', 'resolvedBy'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('security.alerts.index', compact('alerts'));
    }

    public function create(): View
    {
        return view('security.alerts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alert_type' => ['required', 'in:fire,medical,security,natural_disaster,other'],
            'message'    => ['required', 'string', 'min:10'],
        ]);

        $data['triggered_by'] = auth()->id();
        $data['is_active']    = true;

        EmergencyAlert::create($data);

        return redirect()->route('security.alerts.index')
            ->with('success', 'Emergency alert triggered successfully. All relevant parties have been notified.');
    }

    public function show(EmergencyAlert $alert): View
    {
        $alert->load(['triggeredBy', 'resolvedBy']);
        return view('security.alerts.show', compact('alert'));
    }

    public function resolve(EmergencyAlert $alert): RedirectResponse
    {
        $alert->update([
            'is_active'   => false,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Alert marked as resolved.');
    }
}
