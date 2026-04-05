<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $announcements = Announcement::published()->active()->forRole($user->role?->name)
            ->latest('published_at')->paginate(15);
        return view('shared.announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        return view('shared.announcements.show', compact('announcement'));
    }
}
