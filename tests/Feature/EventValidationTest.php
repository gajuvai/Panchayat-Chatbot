<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $this->admin = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_admin_can_create_event_with_future_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'title'       => 'Annual Meet',
                'description' => 'Community annual meeting',
                'venue'       => 'Community Hall',
                'event_date'  => now()->addDays(5)->format('Y-m-d H:i'),
                'end_date'    => now()->addDays(5)->addHours(2)->format('Y-m-d H:i'),
            ])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('events', ['title' => 'Annual Meet']);
    }

    public function test_admin_cannot_create_event_with_past_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'title'       => 'Old Event',
                'description' => 'Past event',
                'venue'       => 'Hall',
                'event_date'  => now()->subDay()->format('Y-m-d H:i'),
                'end_date'    => now()->addHour()->format('Y-m-d H:i'),
            ])
            ->assertSessionHasErrors('event_date');
    }

    public function test_admin_cannot_update_event_to_past_date(): void
    {
        $event = Event::create([
            'user_id'     => $this->admin->id,
            'title'       => 'Future Event',
            'description' => 'desc',
            'venue'       => 'Hall',
            'event_date'  => now()->addDays(3),
            'end_date'    => now()->addDays(3)->addHours(2),
            'status'      => 'upcoming',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.events.update', $event), [
                'title'       => 'Updated Event',
                'description' => 'desc',
                'venue'       => 'Hall',
                'event_date'  => now()->subDay()->format('Y-m-d H:i'),
                'end_date'    => now()->addHour()->format('Y-m-d H:i'),
                'status'      => 'upcoming',
            ])
            ->assertSessionHasErrors('event_date');
    }

    public function test_end_date_must_be_after_event_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), [
                'title'       => 'Event',
                'description' => 'desc',
                'venue'       => 'Hall',
                'event_date'  => now()->addDays(2)->format('Y-m-d H:i'),
                'end_date'    => now()->addDays(1)->format('Y-m-d H:i'),  // before event_date
            ])
            ->assertSessionHasErrors('end_date');
    }

    public function test_admin_can_view_events_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.events.index'))
            ->assertOk();
    }

    public function test_resident_cannot_access_admin_events(): void
    {
        $residentRole = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $resident = User::factory()->create(['role_id' => $residentRole->id]);

        $this->actingAs($resident)
            ->get(route('admin.events.index'))
            ->assertForbidden();
    }
}
