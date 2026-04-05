<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with('author')->latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'type'        => ['required', 'in:general,urgent,maintenance,event'],
            'target_role' => ['nullable', 'in:resident,admin,security_head'],
            'expires_at'  => ['nullable', 'date', 'after:now'],
        ]);

        $announcement = $request->user()->announcements()->create($data);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created.');
    }

    public function show(Announcement $announcement): View
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'type'        => ['required', 'in:general,urgent,maintenance,event'],
            'target_role' => ['nullable', 'in:resident,admin,security_head'],
            'expires_at'  => ['nullable', 'date'],
        ]);

        $announcement->update($data);
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_published' => !$announcement->is_published,
            'published_at' => $announcement->is_published ? null : now(),
        ]);
        $msg = $announcement->is_published ? 'published' : 'unpublished';
        return back()->with('success', "Announcement {$msg}.");
    }
}
