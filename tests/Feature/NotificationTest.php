<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
    }

    public function test_unread_endpoint_returns_json(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('notifications.unread'))
            ->assertOk()
            ->assertJsonStructure(['count', 'notifications']);
    }

    public function test_unread_count_is_zero_for_new_user(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('notifications.unread'))
            ->assertOk()
            ->assertJson(['count' => 0]);
    }

    public function test_unread_count_increments_with_notification(): void
    {
        DatabaseNotification::create([
            'id'             => \Illuminate\Support\Str::uuid(),
            'type'           => 'App\\Notifications\\TestNotification',
            'notifiable_type'=> User::class,
            'notifiable_id'  => $this->user->id,
            'data'           => json_encode(['title' => 'Test', 'message' => 'Hello']),
            'read_at'        => null,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('notifications.unread'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_mark_single_notification_as_read(): void
    {
        $notif = DatabaseNotification::create([
            'id'             => \Illuminate\Support\Str::uuid(),
            'type'           => 'App\\Notifications\\TestNotification',
            'notifiable_type'=> User::class,
            'notifiable_id'  => $this->user->id,
            'data'           => json_encode(['title' => 'Test', 'message' => 'Hello']),
            'read_at'        => null,
        ]);

        $this->actingAs($this->user)
            ->patch(route('notifications.read', $notif->id))
            ->assertRedirect();

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_mark_all_notifications_as_read(): void
    {
        foreach (range(1, 3) as $i) {
            DatabaseNotification::create([
                'id'             => \Illuminate\Support\Str::uuid(),
                'type'           => 'App\\Notifications\\TestNotification',
                'notifiable_type'=> User::class,
                'notifiable_id'  => $this->user->id,
                'data'           => json_encode(['title' => "Test $i", 'message' => "Hello $i"]),
                'read_at'        => null,
            ]);
        }

        $this->actingAs($this->user)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }

    public function test_notifications_index_is_accessible(): void
    {
        $this->actingAs($this->user)
            ->get(route('notifications.index'))
            ->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $this->getJson(route('notifications.unread'))
            ->assertUnauthorized();
    }
}
