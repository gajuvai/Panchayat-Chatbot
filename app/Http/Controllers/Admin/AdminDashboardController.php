<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Poll;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_complaints'    => Complaint::count(),
            'open_complaints'     => Complaint::where('status', 'open')->count(),
            'in_progress'         => Complaint::where('status', 'in_progress')->count(),
            'resolved_today'      => Complaint::where('status', 'resolved')
                ->whereDate('resolved_at', today())->count(),
            'total_residents'     => User::whereHas('role', fn($q) => $q->where('name', 'resident'))->count(),
            'active_events'       => Event::where('status', 'upcoming')->count(),
            'active_polls'        => Poll::where('is_active', true)->where('ends_at', '>', now())->count(),
        ];

        $recentComplaints = Complaint::with(['user', 'category'])
            ->latest()->take(10)->get();

        $urgentComplaints = Complaint::with(['user', 'category'])
            ->where('priority', 'urgent')->where('status', '!=', 'resolved')
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentComplaints', 'urgentComplaints'));
    }
}
