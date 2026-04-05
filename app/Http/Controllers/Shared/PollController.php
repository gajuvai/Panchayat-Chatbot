<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::where('is_active', true)->latest()->paginate(10);
        return view('shared.polls.index', compact('polls'));
    }

    public function show(Poll $poll)
    {
        $poll->load('options', 'creator');
        $hasVoted = $poll->hasUserVoted(auth()->user());
        $canShowResults = $poll->canShowResults();
        return view('shared.polls.show', compact('poll', 'hasVoted', 'canShowResults'));
    }

    public function vote(Request $request, Poll $poll): RedirectResponse
    {
        if (!$poll->isActiveNow()) {
            return back()->with('error', 'This poll is no longer active.');
        }

        if ($poll->hasUserVoted($request->user())) {
            return back()->with('error', 'You have already voted in this poll.');
        }

        $data = $request->validate([
            'option_id' => ['required', 'exists:poll_options,id'],
        ]);

        $option = PollOption::where('id', $data['option_id'])
            ->where('poll_id', $poll->id)->firstOrFail();

        PollVote::create([
            'poll_id'        => $poll->id,
            'poll_option_id' => $option->id,
            'user_id'        => $poll->is_anonymous ? null : $request->user()->id,
            'ip_address'     => $request->ip(),
        ]);

        $option->increment('vote_count');

        return back()->with('success', 'Your vote has been recorded.');
    }
}
