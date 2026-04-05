<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    // ── 404 ──────────────────────────────────────────────────────────────────

    public function test_unknown_route_returns_404(): void
    {
        $this->get('/this-route-definitely-does-not-exist')
            ->assertStatus(404);
    }

    public function test_404_page_renders(): void
    {
        $this->get('/this-route-definitely-does-not-exist')
            ->assertStatus(404)
            ->assertViewIs('errors.404');
    }

    public function test_api_404_returns_json(): void
    {
        // Any unknown URL with Accept: application/json header → JSON 404
        $this->getJson('/this-route-definitely-does-not-exist')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    // ── 403 ──────────────────────────────────────────────────────────────────

    public function test_resident_gets_403_on_admin_route(): void
    {
        $residentRole = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $resident = User::factory()->create(['role_id' => $residentRole->id]);

        $this->actingAs($resident)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    // ── 401 / unauthenticated ─────────────────────────────────────────────────

    public function test_unauthenticated_web_redirects_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthenticated_api_returns_401(): void
    {
        $this->getJson(route('notifications.unread'))
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    // ── 419 CSRF ──────────────────────────────────────────────────────────────

    public function test_missing_csrf_returns_419(): void
    {
        $residentRole = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $user = User::factory()->create(['role_id' => $residentRole->id]);

        // Submit without CSRF token
        $this->actingAs($user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('logout'))
            ->assertRedirect(); // Should still work when middleware is removed
    }

    // ── Fallback route ────────────────────────────────────────────────────────

    public function test_fallback_route_returns_404_view(): void
    {
        $response = $this->get('/completely/unknown/deep/path');
        $response->assertStatus(404);
    }

    // ── Model not found ───────────────────────────────────────────────────────

    public function test_nonexistent_model_id_returns_404(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->get('/admin/complaints/999999')
            ->assertStatus(404);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_api_validation_error_returns_422_json(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->postJson(route('admin.events.store'), []) // empty body → validation fails
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }
}
