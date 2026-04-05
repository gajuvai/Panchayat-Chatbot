<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function upvote(Request $request, ForumThread $thread, ForumReply $reply): RedirectResponse
    {
        $reply->increment('upvotes');

        return back();
    }

    public function destroy(ForumReply $reply): RedirectResponse
    {
        $this->authorize('delete', $reply);
        $reply->delete();

        return back()->with('success', 'Reply deleted.');
    }
}
