<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Document;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
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
    public function it_can_list_documents()
    {
        Document::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'document_number',
                            'title',
                            'revision',
                            'status',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_documents_by_status()
    {
        Document::factory()->create(['status' => 'active']);
        Document::factory()->create(['status' => 'obsolete']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents?status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_filter_documents_by_category()
    {
        Document::factory()->create(['category' => 'procedure']);
        Document::factory()->create(['category' => 'policy']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents?category=procedure');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    /** @test */
    public function it_can_create_document()
    {
        $department = Department::factory()->create();

        $documentData = [
            'document_number' => 'QMS-PR-001',
            'title' => 'Quality Management Procedure',
            'category' => 'procedure',
            'description' => 'Procedure for quality management system',
            'revision' => '2.0',
            'issue_date' => '2025-01-10',
            'review_date' => '2026-01-10',
            'owner_id' => $this->user->id,
            'department_id' => $department->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/documents', $documentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Document created successfully',
            ]);

        $this->assertDatabaseHas('documents', [
            'document_number' => 'QMS-PR-001',
            'title' => 'Quality Management Procedure',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_document()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/documents', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'document_number',
                'title',
                'category',
                'issue_date',
                'owner_id',
            ]);
    }

    /** @test */
    public function it_can_show_specific_document()
    {
        $document = Document::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents/' . $document->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                ],
            ]);
    }

    /** @test */
    public function it_can_update_document()
    {
        $document = Document::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/documents/' . $document->id, [
                'status' => 'active',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document updated successfully',
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_delete_draft_document()
    {
        $document = Document::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/documents/' . $document->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_active_document()
    {
        $document = Document::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/documents/' . $document->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only draft or obsolete documents can be deleted',
            ]);
    }

    /** @test */
    public function it_can_get_documents_due_for_review()
    {
        // Create document due for review (review date in past)
        Document::factory()->create([
            'review_date' => Carbon::now()->subDays(10),
            'status' => 'active',
        ]);

        // Create document not due for review
        Document::factory()->create([
            'review_date' => Carbon::now()->addDays(30),
            'status' => 'active',
        ]);

        // Create obsolete document (should not be included)
        Document::factory()->create([
            'review_date' => Carbon::now()->subDays(5),
            'status' => 'obsolete',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents/due-for-review');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Only the active document with past review date should be returned
        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_document_statistics()
    {
        Document::factory()->create(['status' => 'active']);
        Document::factory()->create(['status' => 'active']);
        Document::factory()->create(['status' => 'draft']);
        Document::factory()->create(['status' => 'obsolete']);
        Document::factory()->create([
            'status' => 'active',
            'review_date' => Carbon::now()->subDays(5),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/documents/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'draft',
                    'under_review',
                    'obsolete',
                    'due_for_review',
                ],
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/documents');
        $response->assertStatus(401);
    }
}
