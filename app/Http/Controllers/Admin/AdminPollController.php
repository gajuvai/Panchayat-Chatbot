<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPollController extends Controller
{
    public function index(): View
    {
        $polls = Poll::withCount('votes')
            ->with('creator')
            ->withCount('options')
            ->latest()
            ->paginate(10);

        return view('admin.polls.index', compact('polls'));
    }

    public function create(): View
    {
        return view('admin.polls.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'options'     => ['required', 'array', 'min:2'],
            'options.*'   => ['required', 'string', 'max:255'],
            'ends_at'     => ['nullable', 'date', 'after:now'],
            'is_active'   => ['boolean'],
        ]);

        $poll = Poll::create([
            'user_id'     => $request->user()->id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'ends_at'     => $data['ends_at'] ?? now()->addYears(10),
            'starts_at'   => now(),
            'is_active'   => $request->boolean('is_active', true),
            'poll_type'   => 'single_choice',
        ]);

        foreach (array_values($data['options']) as $index => $text) {
            $poll->options()->create([
                'option_text'  => $text,
                'option_order' => $index + 1,
                'vote_count'   => 0,
            ]);
        }

        return redirect()->route('admin.polls.index')
            ->with('success', 'Poll created successfully.');
    }

    public function show(Poll $poll): View
    {
        $poll->load(['creator', 'options.votes', 'votes']);
        $totalVotes = $poll->votes->count();

        return view('admin.polls.show', compact('poll', 'totalVotes'));
    }

    public function edit(Poll $poll): View
    {
        $poll->load('options');
        return view('admin.polls.edit', compact('poll'));
    }

    public function update(Request $request, Poll $poll): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'options'     => ['required', 'array', 'min:2'],
            'options.*'   => ['required', 'string', 'max:255'],
            'ends_at'     => ['nullable', 'date'],
            'is_active'   => ['boolean'],
        ]);

        $poll->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'ends_at'     => $data['ends_at'] ?? now()->addYears(10),
            'is_active'   => $request->boolean('is_active'),
        ]);

        // Replace all options (preserving existing vote counts where text matches)
        $existingOptions = $poll->options()->get()->keyBy('option_text');

        $poll->options()->delete();

        foreach (array_values($data['options']) as $index => $text) {
            $existingVoteCount = $existingOptions->get($text)?->vote_count ?? 0;
            $poll->options()->create([
                'option_text'  => $text,
                'option_order' => $index + 1,
                'vote_count'   => $existingVoteCount,
            ]);
        }

        return redirect()->route('admin.polls.show', $poll)
            ->with('success', 'Poll updated successfully.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        $poll->delete();

        return redirect()->route('admin.polls.index')
            ->with('success', 'Poll deleted.');
    }
}
