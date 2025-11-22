<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Certificate;
use App\Models\Department;
use App\Models\Sector;
use App\Models\ExternalAudit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class CertificateControllerTest extends TestCase
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
    public function it_can_list_certificates()
    {
        Certificate::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'certificate_number',
                            'certificate_name',
                            'certificate_type',
                            'issuing_authority',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_certificates_by_status()
    {
        Certificate::factory()->create(['status' => 'active']);
        Certificate::factory()->create(['status' => 'expired']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates?status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_certificates_by_type()
    {
        Certificate::factory()->create(['certificate_type' => 'iso_certification']);
        Certificate::factory()->create(['certificate_type' => 'accreditation']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates?certificate_type=iso_certification');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_certificate()
    {
        $department = Department::factory()->create();
        $audit = ExternalAudit::factory()->create();

        $certificateData = [
            'certificate_number' => 'CERT-ISO-2025-001',
            'certificate_name' => 'ISO 9001:2015 Certification',
            'certificate_type' => 'iso_certification',
            'issuing_authority' => 'BSI',
            'issue_date' => '2025-01-15',
            'expiry_date' => '2028-01-15',
            'scope' => 'Design, manufacture and supply of automotive components',
            'department_id' => $department->id,
            'audit_id' => $audit->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/certificates', $certificateData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Certificate created successfully',
            ]);

        $this->assertDatabaseHas('certificates', [
            'certificate_number' => 'CERT-ISO-2025-001',
            'certificate_type' => 'iso_certification',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_certificate()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/certificates', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'certificate_number',
                'certificate_name',
                'certificate_type',
                'issuing_authority',
                'issue_date',
                'expiry_date',
            ]);
    }

    /** @test */
    public function it_can_show_specific_certificate()
    {
        $certificate = Certificate::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates/' . $certificate->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_certificate()
    {
        $certificate = Certificate::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/certificates/' . $certificate->id, [
                'status' => 'suspended',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Certificate updated successfully',
            ]);

        $this->assertDatabaseHas('certificates', [
            'id' => $certificate->id,
            'status' => 'suspended',
        ]);
    }

    /** @test */
    public function it_can_delete_revoked_certificate()
    {
        $certificate = Certificate::factory()->create([
            'status' => 'revoked',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/certificates/' . $certificate->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Certificate deleted successfully',
            ]);

        $this->assertDatabaseMissing('certificates', [
            'id' => $certificate->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_active_certificate()
    {
        $certificate = Certificate::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/certificates/' . $certificate->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only expired or revoked certificates can be deleted',
            ]);
    }

    /** @test */
    public function it_can_get_expiring_certificates()
    {
        // Create certificate expiring in 15 days
        Certificate::factory()->create([
            'expiry_date' => Carbon::now()->addDays(15),
        ]);

        // Create certificate expiring in 60 days
        Certificate::factory()->create([
            'expiry_date' => Carbon::now()->addDays(60),
        ]);

        // Create already expired certificate
        Certificate::factory()->create([
            'expiry_date' => Carbon::now()->subDays(10),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates/expiring?days=30');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Only the certificate expiring in 15 days should be returned
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_certificate_statistics()
    {
        Certificate::factory()->create(['status' => 'active']);
        Certificate::factory()->create(['status' => 'active']);
        Certificate::factory()->create(['status' => 'expired']);
        Certificate::factory()->create([
            'status' => 'active',
            'expiry_date' => Carbon::now()->addDays(20),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'expired',
                    'expiring_soon',
                    'expiring_30_days',
                    'expiring_90_days',
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/certificates');
        $response->assertStatus(401);
    }
}
