<?php

namespace Tests\Unit\Models;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid AuditPlan with all required fields.
     */
    private function createValidAuditPlan(array $attributes = []): AuditPlan
    {
        $user = User::factory()->create();

        $defaults = [
            'title' => 'Test Audit Plan',
            'description' => 'Test description',
            'audit_type' => 'internal',
            'scope' => 'Test scope',
            'objectives' => 'Test objectives',
            'lead_auditor_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'draft',
            'is_active' => true,
        ];

        return AuditPlan::create(array_merge($defaults, $attributes));
    }

    /**
     * Test status color attribute returns correct colors.
     */
    public function test_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'draft' => 'secondary',
            'planned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $plan = new AuditPlan(['status' => $status]);
            $this->assertEquals($expectedColor, $plan->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test unknown status returns secondary color.
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $plan = new AuditPlan(['status' => 'unknown']);
        $this->assertEquals('secondary', $plan->status_color);
    }

    /**
     * Test audit type label attribute.
     */
    public function test_audit_type_label_attribute(): void
    {
        $typeLabels = [
            'internal' => 'Internal Audit',
            'external' => 'External Audit',
            'compliance' => 'Compliance Audit',
            'operational' => 'Operational Audit',
            'financial' => 'Financial Audit',
            'it' => 'IT Audit',
            'quality' => 'Quality Audit',
        ];

        foreach ($typeLabels as $type => $expectedLabel) {
            $plan = new AuditPlan(['audit_type' => $type]);
            $this->assertEquals($expectedLabel, $plan->audit_type_label, "Failed for type: {$type}");
        }
    }

    /**
     * Test unknown audit type returns ucfirst label.
     */
    public function test_unknown_audit_type_returns_ucfirst_label(): void
    {
        $plan = new AuditPlan(['audit_type' => 'special']);
        $this->assertEquals('Special', $plan->audit_type_label);
    }

    /**
     * Test isOverdue returns false when completed.
     */
    public function test_is_overdue_returns_false_when_completed(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'completed']);
        $this->assertFalse($plan->isOverdue());
    }

    /**
     * Test isOverdue returns false when cancelled.
     */
    public function test_is_overdue_returns_false_when_cancelled(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'cancelled']);
        $this->assertFalse($plan->isOverdue());
    }

    /**
     * Test leadAuditor relationship.
     */
    public function test_lead_auditor_relationship(): void
    {
        $user = User::factory()->create();
        $plan = $this->createValidAuditPlan(['lead_auditor_id' => $user->id]);

        $this->assertInstanceOf(User::class, $plan->leadAuditor);
        $this->assertEquals($user->id, $plan->leadAuditor->id);
    }

    /**
     * Test creator relationship.
     */
    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $plan = $this->createValidAuditPlan(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $plan->creator);
        $this->assertEquals($user->id, $plan->creator->id);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        $this->createValidAuditPlan(['title' => 'Active Plan', 'is_active' => true]);
        $this->createValidAuditPlan(['title' => 'Inactive Plan', 'is_active' => false]);

        $activePlans = AuditPlan::active()->get();

        $this->assertEquals(1, $activePlans->count());
        $this->assertEquals('Active Plan', $activePlans->first()->title);
    }

    /**
     * Test byStatus scope.
     */
    public function test_by_status_scope(): void
    {
        $this->createValidAuditPlan(['title' => 'Draft Plan', 'status' => 'draft']);
        $this->createValidAuditPlan(['title' => 'Completed Plan', 'status' => 'completed']);

        $draftPlans = AuditPlan::byStatus('draft')->get();

        $this->assertEquals(1, $draftPlans->count());
        $this->assertEquals('Draft Plan', $draftPlans->first()->title);
    }

    /**
     * Test departments relationship.
     */
    public function test_departments_relationship(): void
    {
        $plan = $this->createValidAuditPlan();
        $department = Department::factory()->create();

        $plan->departments()->attach($department->id, [
            'planned_start_date' => now(),
            'planned_end_date' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $this->assertCount(1, $plan->departments);
        $this->assertEquals($department->id, $plan->departments->first()->id);
    }

    /**
     * Test departments pivot data is loaded.
     */
    public function test_departments_pivot_data_is_loaded(): void
    {
        $plan = $this->createValidAuditPlan();
        $department = Department::factory()->create();

        $plan->departments()->attach($department->id, [
            'planned_start_date' => now(),
            'planned_end_date' => now()->addDays(7),
            'status' => 'pending',
            'notes' => 'Test notes',
        ]);

        $attachedDept = $plan->departments()->first();

        $this->assertNotNull($attachedDept->pivot->planned_start_date);
        $this->assertNotNull($attachedDept->pivot->planned_end_date);
        $this->assertEquals('pending', $attachedDept->pivot->status);
        $this->assertEquals('Test notes', $attachedDept->pivot->notes);
    }

    /**
     * Test date casting.
     */
    public function test_dates_are_cast_correctly(): void
    {
        $plan = new AuditPlan([
            'actual_start_date' => '2025-01-15',
            'actual_end_date' => '2025-01-20',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $plan->actual_start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $plan->actual_end_date);
    }

    /**
     * Test is_active is cast to boolean.
     */
    public function test_is_active_is_cast_to_boolean(): void
    {
        $plan = new AuditPlan(['is_active' => 1]);
        $this->assertIsBool($plan->is_active);
        $this->assertTrue($plan->is_active);

        $plan = new AuditPlan(['is_active' => 0]);
        $this->assertFalse($plan->is_active);
    }

    /**
     * Test soft deletes.
     */
    public function test_uses_soft_deletes(): void
    {
        $plan = $this->createValidAuditPlan();
        $planId = $plan->id;
        $plan->delete();

        $this->assertNull(AuditPlan::find($planId));
        $this->assertNotNull(AuditPlan::withTrashed()->find($planId));
    }
}
