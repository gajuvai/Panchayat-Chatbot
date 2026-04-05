<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Poll;
use Illuminate\Http\Request;

class ResidentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total'       => $user->complaints()->count(),
            'open'        => $user->complaints()->where('status', 'open')->count(),
            'in_progress' => $user->complaints()->where('status', 'in_progress')->count(),
            'resolved'    => $user->complaints()->where('status', 'resolved')->count(),
        ];

        $recentComplaints = $user->complaints()->with('category')->latest()->take(5)->get();

        $announcements = Announcement::published()->active()->forRole('resident')
            ->latest('published_at')->take(5)->get();

        $upcomingEvents = Event::where('status', 'upcoming')
            ->where('event_date', '>', now())->orderBy('event_date')->take(3)->get();

        $activePolls = Poll::where('is_active', true)->where('ends_at', '>', now())->take(3)->get();

        return view('resident.dashboard', compact(
            'stats', 'recentComplaints', 'announcements', 'upcomingEvents', 'activePolls'
        ));
    }
}
