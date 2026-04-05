<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $query = ForumThread::approved()->with(['author', 'replies'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $threads = $query->paginate(15)->withQueryString();

        return view('shared.forum.index', compact('threads'));
    }

    public function create(): View
    {
        return view('shared.forum.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ]);

        $data['user_id']    = $request->user()->id;
        $data['is_approved'] = true;

        $thread = ForumThread::create($data);

        return redirect()->route('forum.show', $thread)
            ->with('success', 'Thread posted successfully.');
    }

    public function show(ForumThread $forum): View
    {
        $forum->load(['author', 'topReplies.author', 'topReplies.children.author']);

        return view('shared.forum.show', compact('forum'));
    }

    public function edit(ForumThread $forum): View
    {
        $this->authorize('update', $forum);

        return view('shared.forum.edit', compact('forum'));
    }

    public function update(Request $request, ForumThread $forum): RedirectResponse
    {
        $this->authorize('update', $forum);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ]);

        $forum->update($data);

        return redirect()->route('forum.show', $forum)
            ->with('success', 'Thread updated.');
    }

    public function destroy(ForumThread $forum): RedirectResponse
    {
        $this->authorize('delete', $forum);
        $forum->delete();

        return redirect()->route('forum.index')
            ->with('success', 'Thread deleted.');
    }
}
