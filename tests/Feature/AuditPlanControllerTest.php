<?php

namespace Tests\Feature;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'quality_manager', 'display_name' => 'Quality Manager']);
        $this->user->roles()->attach($role->id);

        $this->department = Department::factory()->create(['is_active' => true]);
    }

    private function createValidAuditPlan(array $attributes = []): AuditPlan
    {
        $defaults = [
            'title' => 'Test Audit Plan',
            'description' => 'Test description',
            'audit_type' => 'internal',
            'scope' => 'Test scope',
            'objectives' => 'Test objectives',
            'lead_auditor_id' => $this->user->id,
            'created_by' => $this->user->id,
            'status' => 'draft',
            'is_active' => true,
        ];

        return AuditPlan::create(array_merge($defaults, $attributes));
    }

    public function test_index_displays_audit_plans(): void
    {
        $plan = $this->createValidAuditPlan();

        $response = $this->actingAs($this->user)->get(route('audit-plans.index'));

        $response->assertStatus(200);
        $response->assertViewIs('audit-plans.index');
        $response->assertViewHas('auditPlans');
        $response->assertViewHas('stats');
        $response->assertSee($plan->title);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('audit-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_filters_by_status(): void
    {
        $draftPlan = $this->createValidAuditPlan(['title' => 'Draft Plan', 'status' => 'draft']);
        $completedPlan = $this->createValidAuditPlan(['title' => 'Completed Plan', 'status' => 'completed']);

        $response = $this->actingAs($this->user)
            ->get(route('audit-plans.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertSee('Draft Plan');
    }

    public function test_index_filters_by_audit_type(): void
    {
        $internalPlan = $this->createValidAuditPlan(['title' => 'Internal Plan', 'audit_type' => 'internal']);
        $externalPlan = $this->createValidAuditPlan(['title' => 'External Plan', 'audit_type' => 'external']);

        $response = $this->actingAs($this->user)
            ->get(route('audit-plans.index', ['audit_type' => 'internal']));

        $response->assertStatus(200);
        $response->assertSee('Internal Plan');
    }

    public function test_index_statistics_are_accurate(): void
    {
        $this->createValidAuditPlan(['status' => 'draft']);
        $this->createValidAuditPlan(['status' => 'draft']);
        $this->createValidAuditPlan(['status' => 'planned']);
        $this->createValidAuditPlan(['status' => 'completed']);

        $response = $this->actingAs($this->user)->get(route('audit-plans.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['draft']);
        $this->assertEquals(1, $stats['planned']);
        $this->assertEquals(1, $stats['completed']);
    }

    public function test_audit_plan_can_be_created_directly(): void
    {
        $plan = $this->createValidAuditPlan([
            'title' => 'Direct Create Test',
            'audit_type' => 'compliance',
        ]);

        $this->assertDatabaseHas('audit_plans', [
            'id' => $plan->id,
            'title' => 'Direct Create Test',
            'audit_type' => 'compliance',
        ]);
    }

    public function test_audit_plan_can_be_soft_deleted(): void
    {
        $plan = $this->createValidAuditPlan();
        $planId = $plan->id;

        $plan->delete();

        $this->assertSoftDeleted('audit_plans', ['id' => $planId]);
    }

    public function test_audit_plan_status_can_be_updated(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'draft']);

        $plan->update(['status' => 'planned']);

        $this->assertDatabaseHas('audit_plans', [
            'id' => $plan->id,
            'status' => 'planned',
        ]);
    }

    public function test_audit_plan_start_workflow(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'planned']);

        $plan->update([
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);

        $this->assertDatabaseHas('audit_plans', [
            'id' => $plan->id,
            'status' => 'in_progress',
        ]);
        $this->assertNotNull($plan->fresh()->actual_start_date);
    }

    public function test_audit_plan_complete_workflow(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'in_progress']);

        $plan->update([
            'status' => 'completed',
            'actual_end_date' => now(),
        ]);

        $this->assertDatabaseHas('audit_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);
        $this->assertNotNull($plan->fresh()->actual_end_date);
    }

    public function test_audit_plan_cancel_workflow(): void
    {
        $plan = $this->createValidAuditPlan(['status' => 'planned']);

        $plan->update(['status' => 'cancelled']);

        $this->assertDatabaseHas('audit_plans', [
            'id' => $plan->id,
            'status' => 'cancelled',
        ]);
    }
}
