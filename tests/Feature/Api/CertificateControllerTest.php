<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Certificate;
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
                            'standard',
                            'certification_body',
                            'certificate_type',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_certificates_by_status()
    {
        Certificate::factory()->create(['status' => 'valid']);
        Certificate::factory()->create(['status' => 'expired']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates?status=valid');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_certificates_by_type()
    {
        Certificate::factory()->create(['certificate_type' => 'initial']);
        Certificate::factory()->create(['certificate_type' => 'renewal']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates?certificate_type=initial');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_certificate()
    {
        $certificateData = [
            'certificate_number' => 'CERT-ISO-2025-001',
            'standard' => 'ISO 9001:2015',
            'certification_body' => 'BSI',
            'certificate_type' => 'initial',
            'issue_date' => '2025-01-15',
            'expiry_date' => '2028-01-15',
            'scope_of_certification' => 'Design, manufacture and supply of automotive components',
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
            'certificate_type' => 'initial',
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
                'standard',
                'certification_body',
                'certificate_type',
                'issue_date',
                'expiry_date',
                'scope_of_certification',
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
            'status' => 'valid',
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

        $this->assertSoftDeleted('certificates', [
            'id' => $certificate->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_valid_certificate()
    {
        $certificate = Certificate::factory()->create([
            'status' => 'valid',
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
            'status' => 'valid',
            'expiry_date' => Carbon::now()->addDays(15),
        ]);

        // Create certificate expiring in 60 days
        Certificate::factory()->create([
            'status' => 'valid',
            'expiry_date' => Carbon::now()->addDays(60),
        ]);

        // Create already expired certificate
        Certificate::factory()->create([
            'status' => 'expired',
            'expiry_date' => Carbon::now()->subDays(10),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates/expiring?days=30');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Only the valid certificate expiring in 15 days should be returned
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_certificate_statistics()
    {
        Certificate::factory()->create(['status' => 'valid']);
        Certificate::factory()->create(['status' => 'valid']);
        Certificate::factory()->create(['status' => 'expired']);
        Certificate::factory()->create([
            'status' => 'expiring_soon',
            'expiry_date' => Carbon::now()->addDays(20),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/certificates/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'valid',
                    'expiring_soon',
                    'expired',
                    'suspended',
                    'revoked',
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
