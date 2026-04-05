<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\EmergencyAlert;
use App\Models\PatrolAssignment;
use App\Models\SecurityIncident;

class SecurityDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_incidents'  => SecurityIncident::where('status', 'active')->count(),
            'active_alerts'     => EmergencyAlert::where('is_active', true)->count(),
            'scheduled_patrols' => PatrolAssignment::where('status', 'scheduled')
                ->where('shift_start', '>=', now())->count(),
            'security_complaints'=> Complaint::whereHas('category', fn($q) =>
                $q->where('name', 'like', '%security%'))->where('status', '!=', 'resolved')->count(),
        ];

        $recentIncidents = SecurityIncident::with('reporter')->latest()->take(10)->get();
        $activeAlerts    = EmergencyAlert::with('triggeredBy')->where('is_active', true)->latest()->get();
        $upcomingPatrols = PatrolAssignment::with(['assignedTo'])
            ->where('status', 'scheduled')->orderBy('shift_start')->take(10)->get();

        return view('security.dashboard', compact('stats', 'recentIncidents', 'activeAlerts', 'upcomingPatrols'));
    }
}
