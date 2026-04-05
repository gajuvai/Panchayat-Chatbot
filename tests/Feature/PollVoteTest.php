<?php

namespace Tests\Feature;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollVoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Poll $poll;
    private PollOption $option;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $creator = User::factory()->create(['role_id' => $adminRole->id]);
        $this->user = User::factory()->create(['role_id' => $role->id]);

        $this->poll = Poll::create([
            'user_id'               => $creator->id,
            'title'                 => 'Test Poll',
            'description'           => 'A test poll',
            'poll_type'             => 'single_choice',
            'starts_at'             => now()->subHour(),
            'ends_at'               => now()->addDay(),
            'is_anonymous'          => false,
            'is_active'             => true,
            'show_results_before_end' => false,
        ]);

        $this->option = PollOption::create([
            'poll_id'      => $this->poll->id,
            'option_text'  => 'Option A',
            'option_order' => 1,
            'vote_count'   => 0,
        ]);
    }

    public function test_user_can_vote_on_active_poll(): void
    {
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id])
            ->assertRedirect();

        $this->assertDatabaseHas('poll_votes', [
            'poll_id'        => $this->poll->id,
            'poll_option_id' => $this->option->id,
            'user_id'        => $this->user->id,
        ]);
    }

    public function test_user_cannot_vote_twice_on_same_poll(): void
    {
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id]);

        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id])
            ->assertRedirect()
            ->assertSessionHas('error', 'You have already voted in this poll.');

        $this->assertEquals(1, PollVote::where('poll_id', $this->poll->id)->where('user_id', $this->user->id)->count());
    }

    public function test_anonymous_poll_still_prevents_double_voting(): void
    {
        $this->poll->update(['is_anonymous' => true]);

        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id]);

        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id])
            ->assertSessionHas('error', 'You have already voted in this poll.');

        // user_id is always stored now (BUG-002 fix)
        $this->assertEquals(1, PollVote::where('poll_id', $this->poll->id)->where('user_id', $this->user->id)->count());
    }

    public function test_vote_increments_option_vote_count(): void
    {
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id]);

        $this->assertEquals(1, $this->option->fresh()->vote_count);
    }

    public function test_cannot_vote_on_inactive_poll(): void
    {
        $this->poll->update(['is_active' => false]);

        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id])
            ->assertSessionHas('error', 'This poll is no longer active.');
    }

    public function test_cannot_vote_on_expired_poll(): void
    {
        $this->poll->update(['ends_at' => now()->subMinute()]);

        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => $this->option->id])
            ->assertSessionHas('error', 'This poll is no longer active.');
    }

    public function test_cannot_vote_with_invalid_option(): void
    {
        $this->actingAs($this->user)
            ->post(route('polls.vote', $this->poll), ['option_id' => 99999])
            ->assertSessionHasErrors('option_id');
    }
}
