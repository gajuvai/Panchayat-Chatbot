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
            'total_complaints' => Complaint::count(),
            'open_complaints'  => Complaint::open()->count(),
            'in_progress'      => Complaint::inProgress()->count(),
            'resolved_today'   => Complaint::resolvedToday()->count(),
            'total_residents'  => User::withRole('resident')->count(),
            'active_events'    => Event::upcoming()->count(),
            'active_polls'     => Poll::activeNowScope()->count(),
        ];

        $recentComplaints = Complaint::with(['user', 'category'])
            ->latest()->take(10)->get();

        $urgentComplaints = Complaint::with(['user', 'category'])
            ->urgentUnresolved()->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentComplaints', 'urgentComplaints'));
    }
}
