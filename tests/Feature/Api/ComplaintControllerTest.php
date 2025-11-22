<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\CustomerComplaint;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintControllerTest extends TestCase
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
    public function it_can_list_complaints()
    {
        CustomerComplaint::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'complaint_number',
                            'complaint_subject',
                            'complaint_description',
                            'customer_name',
                            'complaint_category',
                            'severity',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_complaints_by_status()
    {
        CustomerComplaint::factory()->create(['status' => 'new']);
        CustomerComplaint::factory()->create(['status' => 'resolved']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints?status=new');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_complaints_by_severity()
    {
        CustomerComplaint::factory()->create(['severity' => 'critical']);
        CustomerComplaint::factory()->create(['severity' => 'minor']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints?severity=critical');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_complaints_by_category()
    {
        CustomerComplaint::factory()->create(['complaint_category' => 'product_quality']);
        CustomerComplaint::factory()->create(['complaint_category' => 'service_quality']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints?category=product_quality');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_complaint()
    {
        $department = Department::factory()->create();

        $complaintData = [
            'complaint_number' => 'COMP-2025-001',
            'complaint_subject' => 'Product defect in batch 123',
            'complaint_description' => 'Customer reported defects in product finish',
            'customer_name' => 'ABC Manufacturing Ltd',
            'customer_email' => 'contact@abc.com',
            'customer_phone' => '+1234567890',
            'complaint_category' => 'product_quality',
            'severity' => 'major',
            'priority' => 'high',
            'complaint_date' => '2025-01-18',
            'assigned_to_user_id' => $this->user->id,
            'assigned_to_department_id' => $department->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/complaints', $complaintData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Complaint created successfully',
            ]);

        $this->assertDatabaseHas('customer_complaints', [
            'complaint_number' => 'COMP-2025-001',
            'severity' => 'major',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_complaint()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/complaints', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'complaint_number',
                'complaint_subject',
                'complaint_description',
                'customer_name',
                'complaint_category',
                'severity',
                'complaint_date',
            ]);
    }

    /** @test */
    public function it_can_show_specific_complaint()
    {
        $complaint = CustomerComplaint::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints/' . $complaint->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $complaint->id,
                    'complaint_number' => $complaint->complaint_number,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_complaint()
    {
        $complaint = CustomerComplaint::factory()->create([
            'status' => 'new',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/complaints/' . $complaint->id, [
                'status' => 'investigating',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Complaint updated successfully',
            ]);

        $this->assertDatabaseHas('customer_complaints', [
            'id' => $complaint->id,
            'status' => 'investigating',
        ]);
    }

    /** @test */
    public function it_can_delete_new_complaint()
    {
        $complaint = CustomerComplaint::factory()->create([
            'status' => 'new',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/complaints/' . $complaint->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Complaint deleted successfully',
            ]);

        $this->assertSoftDeleted('customer_complaints', [
            'id' => $complaint->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_resolved_complaint()
    {
        $complaint = CustomerComplaint::factory()->create([
            'status' => 'resolved',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/complaints/' . $complaint->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only new complaints can be deleted',
            ]);
    }

    /** @test */
    public function it_can_get_unresolved_complaints()
    {
        CustomerComplaint::factory()->create(['status' => 'new']);
        CustomerComplaint::factory()->create(['status' => 'investigating']);
        CustomerComplaint::factory()->create(['status' => 'acknowledged']);
        CustomerComplaint::factory()->create(['status' => 'resolved']); // Should not be included
        CustomerComplaint::factory()->create(['status' => 'closed']); // Should not be included

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints/unresolved');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Only unresolved complaints (new, investigating, acknowledged, escalated) should be returned
        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_complaint_statistics()
    {
        CustomerComplaint::factory()->create(['status' => 'new']);
        CustomerComplaint::factory()->create(['status' => 'investigating']);
        CustomerComplaint::factory()->create(['status' => 'resolved']);
        CustomerComplaint::factory()->create(['status' => 'closed']);
        CustomerComplaint::factory()->create(['severity' => 'critical']);
        CustomerComplaint::factory()->create(['priority' => 'high']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/complaints/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'new',
                    'acknowledged',
                    'investigating',
                    'resolved',
                    'closed',
                    'escalated',
                    'critical_severity',
                    'major_severity',
                    'critical_priority',
                    'high_priority',
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/complaints');
        $response->assertStatus(401);
    }
}
