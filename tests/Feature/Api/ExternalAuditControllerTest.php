<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\ExternalAudit;
use App\Models\Department;
use App\Models\Sector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalAuditControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test_device')->plainTextToken;
    }

    /** @test */
    public function it_can_list_audits()
    {
        ExternalAudit::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'audit_number',
                            'audit_type',
                            'standard',
                            'certification_body',
                            'status',
                        ],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_audits_by_status()
    {
        ExternalAudit::factory()->create(['status' => 'scheduled']);
        ExternalAudit::factory()->create(['status' => 'completed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits?status=scheduled');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_audits_by_type()
    {
        ExternalAudit::factory()->create(['audit_type' => 'surveillance']);
        ExternalAudit::factory()->create(['audit_type' => 'recertification']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits?audit_type=surveillance');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_audit()
    {
        $department = Department::factory()->create();
        $sector = Sector::factory()->create();

        $auditData = [
            'audit_number' => 'EA-2025-001',
            'audit_type' => 'surveillance',
            'standard' => 'ISO 9001:2015',
            'certification_body' => 'BSI',
            'lead_auditor_name' => 'Jane Smith',
            'lead_auditor_email' => 'jane@bsi.com',
            'scheduled_start_date' => '2025-03-01',
            'scheduled_end_date' => '2025-03-03',
            'scope' => 'Manufacturing processes',
            'department_id' => $department->id,
            'sector_id' => $sector->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/audits', $auditData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Audit created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'audit_number',
                    'audit_type',
                ],
            ]);

        $this->assertDatabaseHas('external_audits', [
            'audit_number' => 'EA-2025-001',
            'audit_type' => 'surveillance',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_audit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/audits', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'audit_number',
                'audit_type',
                'standard',
                'certification_body',
                'lead_auditor_name',
                'scheduled_start_date',
                'scheduled_end_date',
            ]);
    }

    /** @test */
    public function it_can_show_specific_audit()
    {
        $audit = ExternalAudit::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits/' . $audit->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $audit->id,
                    'audit_number' => $audit->audit_number,
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_audit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'External audit not found',
            ]);
    }

    /** @test */
    public function it_can_update_audit()
    {
        $audit = ExternalAudit::factory()->create([
            'status' => 'scheduled',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/audits/' . $audit->id, [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Audit updated successfully',
            ]);

        $this->assertDatabaseHas('external_audits', [
            'id' => $audit->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function it_can_delete_scheduled_audit()
    {
        $audit = ExternalAudit::factory()->create([
            'status' => 'scheduled',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/audits/' . $audit->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Audit deleted successfully',
            ]);

        $this->assertDatabaseMissing('external_audits', [
            'id' => $audit->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_completed_audit()
    {
        $audit = ExternalAudit::factory()->create([
            'status' => 'completed',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/audits/' . $audit->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only scheduled audits can be deleted',
            ]);
    }

    /** @test */
    public function it_can_get_audit_statistics()
    {
        ExternalAudit::factory()->create(['status' => 'scheduled']);
        ExternalAudit::factory()->create(['status' => 'in_progress']);
        ExternalAudit::factory()->count(2)->create(['status' => 'completed', 'result' => 'passed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/audits/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 4,
                    'scheduled' => 1,
                    'in_progress' => 1,
                    'completed' => 2,
                    'passed' => 2,
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/audits');
        $response->assertStatus(401);
    }
}
