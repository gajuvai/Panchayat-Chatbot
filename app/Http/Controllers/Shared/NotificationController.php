<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('shared.notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * JSON endpoint for real-time polling.
     * Returns unread count and last 5 notifications.
     */
    public function unread()
    {
        $user = auth()->user();

        $count = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
                'human_time' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count'         => $count,
            'notifications' => $notifications,
        ]);
    }
}
