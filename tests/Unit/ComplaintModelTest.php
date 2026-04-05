<?php

namespace Tests\Unit;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintModelTest extends TestCase
{
    use RefreshDatabase;

    private User $resident;
    private ComplaintCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'resident', 'display_name' => 'Resident']);
        $this->resident = User::factory()->create(['role_id' => $role->id]);
        $this->category = ComplaintCategory::create([
            'name'      => 'General',
            'is_active' => true,
        ]);
    }

    public function test_complaint_number_is_auto_generated(): void
    {
        $complaint = Complaint::create([
            'user_id'     => $this->resident->id,
            'category_id' => $this->category->id,
            'title'       => 'Broken light',
            'description' => 'Street light broken.',
            'status'      => 'open',
            'priority'    => 'medium',
        ]);

        $this->assertNotNull($complaint->complaint_number);
        $this->assertStringStartsWith('CMP-' . date('Y') . '-', $complaint->complaint_number);
    }

    public function test_complaint_numbers_are_unique(): void
    {
        $c1 = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Complaint 1', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);
        $c2 = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Complaint 2', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);

        $this->assertNotEquals($c1->complaint_number, $c2->complaint_number);
    }

    public function test_open_scope_returns_only_open_complaints(): void
    {
        Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Open', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);
        Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Resolved', 'description' => 'desc', 'status' => 'resolved', 'priority' => 'low',
        ]);

        $open = Complaint::open()->get();
        $this->assertCount(1, $open);
        $this->assertEquals('Open', $open->first()->title);
    }

    public function test_is_upvoted_by_returns_false_when_not_upvoted(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Test', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);

        $this->assertFalse($complaint->isUpvotedBy($this->resident));
    }

    public function test_complaint_belongs_to_user(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Test', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);

        $this->assertEquals($this->resident->id, $complaint->user->id);
    }

    public function test_complaint_belongs_to_category(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Test', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);

        $this->assertEquals($this->category->id, $complaint->category->id);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->resident->id, 'category_id' => $this->category->id,
            'title' => 'Test', 'description' => 'desc', 'status' => 'open', 'priority' => 'low',
        ]);

        $this->assertInstanceOf(\App\Enums\ComplaintStatus::class, $complaint->status);
    }
}
