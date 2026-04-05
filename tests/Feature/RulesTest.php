<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RuleBookSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $resident;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole    = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $residentRole = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $this->admin    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->resident = User::factory()->create(['role_id' => $residentRole->id]);
    }

    public function test_public_rules_index_is_accessible_when_authenticated(): void
    {
        RuleBookSection::create([
            'user_id'       => $this->admin->id,
            'title'         => 'Parking Rules',
            'content'       => 'No double parking.',
            'section_order' => 1,
            'is_published'  => true,
        ]);

        $this->actingAs($this->resident)
            ->get(route('rules.index'))
            ->assertOk()
            ->assertSee('Parking Rules');
    }

    public function test_public_rules_show_returns_published_section(): void
    {
        $section = RuleBookSection::create([
            'user_id'       => $this->admin->id,
            'title'         => 'Noise Policy',
            'content'       => 'Quiet hours are 10 PM to 7 AM.',
            'section_order' => 2,
            'is_published'  => true,
        ]);

        $this->actingAs($this->resident)
            ->get(route('rules.show', $section))
            ->assertOk()
            ->assertSee('Noise Policy');
    }

    public function test_public_rules_show_returns_404_for_unpublished_section(): void
    {
        $section = RuleBookSection::create([
            'user_id'       => $this->admin->id,
            'title'         => 'Draft Section',
            'content'       => 'Not yet ready.',
            'section_order' => 3,
            'is_published'  => false,
        ]);

        $this->actingAs($this->resident)
            ->get(route('rules.show', $section))
            ->assertNotFound();
    }

    public function test_admin_can_create_rule_section(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rules.store'), [
                'title'         => 'Visitor Policy',
                'content'       => 'Visitors must register at the gate.',
                'section_order' => 1,
                'is_published'  => true,
            ])
            ->assertRedirect(route('admin.rules.index'));

        $this->assertDatabaseHas('rule_book_sections', ['title' => 'Visitor Policy']);
    }

    public function test_admin_rules_create_gives_correct_next_order_when_empty(): void
    {
        // When no sections exist, next order should be 1
        $this->actingAs($this->admin)
            ->get(route('admin.rules.create'))
            ->assertOk()
            ->assertViewHas('nextOrder', 1);
    }

    public function test_admin_can_delete_rule_section(): void
    {
        $section = RuleBookSection::create([
            'user_id'       => $this->admin->id,
            'title'         => 'To Delete',
            'content'       => 'Will be removed.',
            'section_order' => 1,
            'is_published'  => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.rules.destroy', $section))
            ->assertRedirect(route('admin.rules.index'));

        $this->assertDatabaseMissing('rule_book_sections', ['id' => $section->id]);
    }

    public function test_resident_cannot_access_admin_rules(): void
    {
        $this->actingAs($this->resident)
            ->get(route('admin.rules.index'))
            ->assertForbidden();
    }
}
