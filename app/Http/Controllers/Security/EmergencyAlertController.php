<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\User;
use App\Notifications\EmergencyAlertNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyAlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = EmergencyAlert::with(['triggeredBy', 'resolvedBy'])
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('alert_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $alerts      = $query->paginate(10)->withQueryString();
        $activeCount = EmergencyAlert::where('is_active', true)->count();

        return view('security.alerts.index', compact('alerts', 'activeCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alert_type' => ['required', 'in:fire,medical,security,natural_disaster,other'],
            'message'    => ['required', 'string', 'min:10'],
        ]);

        $data['triggered_by'] = auth()->id();
        $data['is_active']    = true;

        $alert = EmergencyAlert::create($data);

        // Notify all residents and admins
        $alert->load('triggeredBy');
        User::whereHas('role', fn ($q) => $q->whereIn('name', ['resident', 'admin']))
            ->each(fn ($user) => $user->notify(new EmergencyAlertNotification($alert)));

        return redirect()->route('security.alerts.index')
            ->with('success', 'Emergency alert triggered. All residents and admins have been notified.');
    }

    public function show(EmergencyAlert $alert): View
    {
        $alert->load(['triggeredBy', 'resolvedBy']);
        return view('security.alerts.show', compact('alert'));
    }

    public function resolve(EmergencyAlert $alert): RedirectResponse
    {
        if (! $alert->is_active) {
            return back()->with('error', 'This alert is already resolved.');
        }

        $alert->update([
            'is_active'   => false,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Alert marked as resolved.');
    }
}
