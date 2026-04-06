<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumReplyController extends Controller
{
    public function store(Request $request, ForumThread $forum): RedirectResponse
    {
        if ($forum->is_locked) {
            return back()->with('error', 'This thread is locked.');
        }

        $data = $request->validate([
            'body'      => ['required', 'string'],
            'parent_id' => ['nullable', 'exists:forum_replies,id'],
        ]);

        $data['user_id']   = $request->user()->id;
        $data['thread_id'] = $forum->id;

        ForumReply::create($data);

        $forum->update(['last_reply_at' => now()]);

        return back()->with('success', 'Reply posted.');
    }

    public function edit(ForumReply $reply): View
    {
        $this->authorize('update', $reply);

        return view('shared.forum.edit_reply', compact('reply'));
    }

    public function update(Request $request, ForumReply $reply): RedirectResponse
    {
        $this->authorize('update', $reply);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $reply->update($data);

        return redirect()->route('forum.show', $reply->thread_id)
            ->with('success', 'Reply updated.');
    }

    public function upvote(Request $request, ForumThread $thread, ForumReply $reply): RedirectResponse
    {
        // Prevent duplicate upvotes using session tracking
        $key = 'reply_upvoted_' . $reply->id;

        if (!session()->has($key)) {
            $reply->increment('upvotes');
            session()->put($key, true);
        }

        return back();
    }

    public function destroy(ForumReply $reply): RedirectResponse
    {
        $this->authorize('delete', $reply);
        $reply->delete();

        return back()->with('success', 'Reply deleted.');
    }
}
