<?php

namespace Tests\Unit\Models;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid Document with all required fields.
     */
    private function createValidDocument(array $attributes = []): Document
    {
        $category = $attributes['category'] ?? 'procedure';

        $defaults = [
            'document_number' => $attributes['document_number'] ?? Document::generateDocumentNumber($category),
            'title' => 'Test Document',
            'category' => $category,
            'status' => 'draft',
        ];

        return Document::create(array_merge($defaults, $attributes));
    }

    /**
     * Test document number generation for different categories
     */
    public function test_generate_document_number_for_each_category(): void
    {
        $categories = [
            'quality_manual' => 'QM',
            'procedure' => 'PROC',
            'work_instruction' => 'WI',
            'form' => 'FORM',
            'record' => 'REC',
            'external_document' => 'EXT',
            'unknown' => 'DOC',
        ];

        foreach ($categories as $category => $expectedPrefix) {
            $docNumber = Document::generateDocumentNumber($category);
            $pattern = "/^{$expectedPrefix}-" . date('Y') . "-\d{4}$/";
            $this->assertMatchesRegularExpression($pattern, $docNumber, "Failed for category: {$category}");
        }
    }

    /**
     * Test document number generation increments correctly
     */
    public function test_generate_document_number_increments_sequentially(): void
    {
        $doc1 = $this->createValidDocument(['category' => 'procedure']);
        $docNumber2 = Document::generateDocumentNumber('procedure');

        $num1 = (int) substr($doc1->document_number, -4);
        $num2 = (int) substr($docNumber2, -4);

        $this->assertEquals($num1 + 1, $num2);
    }

    /**
     * Test document number generation handles soft-deleted records
     */
    public function test_generate_document_number_includes_soft_deleted(): void
    {
        $doc = $this->createValidDocument(['category' => 'form']);
        $deletedNumber = $doc->document_number;
        $doc->delete();

        $newNumber = Document::generateDocumentNumber('form');

        $this->assertNotEquals($deletedNumber, $newNumber);

        $deletedNum = (int) substr($deletedNumber, -4);
        $newNum = (int) substr($newNumber, -4);
        $this->assertGreaterThan($deletedNum, $newNum);
    }

    /**
     * Test version increment for draft document
     */
    public function test_increment_version_for_draft(): void
    {
        $doc = new Document([
            'version' => '1.0',
            'status' => 'draft',
        ]);

        $newVersion = $doc->incrementVersion();

        $this->assertEquals('1.1', $newVersion);
    }

    /**
     * Test version increment for approved document
     */
    public function test_increment_version_for_approved(): void
    {
        $doc = new Document([
            'version' => '1.5',
            'status' => 'approved',
        ]);

        $newVersion = $doc->incrementVersion();

        $this->assertEquals('2.0', $newVersion);
    }

    /**
     * Test version increment for effective document
     */
    public function test_increment_version_for_effective(): void
    {
        $doc = new Document([
            'version' => '2.3',
            'status' => 'effective',
        ]);

        $newVersion = $doc->incrementVersion();

        $this->assertEquals('3.0', $newVersion);
    }

    /**
     * Test status check methods
     */
    public function test_status_check_methods(): void
    {
        $doc = new Document(['status' => 'draft']);
        $this->assertTrue($doc->isDraft());
        $this->assertFalse($doc->isEffective());
        $this->assertFalse($doc->isObsolete());

        $doc = new Document(['status' => 'effective']);
        $this->assertFalse($doc->isDraft());
        $this->assertTrue($doc->isEffective());
        $this->assertFalse($doc->isObsolete());

        $doc = new Document(['status' => 'obsolete']);
        $this->assertFalse($doc->isDraft());
        $this->assertFalse($doc->isEffective());
        $this->assertTrue($doc->isObsolete());
    }

    /**
     * Test canBeEdited method
     */
    public function test_can_be_edited_returns_correct_values(): void
    {
        $draftDoc = new Document(['status' => 'draft']);
        $this->assertTrue($draftDoc->canBeEdited());

        $pendingReviewDoc = new Document(['status' => 'pending_review']);
        $this->assertTrue($pendingReviewDoc->canBeEdited());

        $effectiveDoc = new Document(['status' => 'effective']);
        $this->assertFalse($effectiveDoc->canBeEdited());
    }

    /**
     * Test canBeReviewed method
     */
    public function test_can_be_reviewed_returns_correct_values(): void
    {
        $pendingReviewDoc = new Document(['status' => 'pending_review']);
        $this->assertTrue($pendingReviewDoc->canBeReviewed());

        $draftDoc = new Document(['status' => 'draft']);
        $this->assertFalse($draftDoc->canBeReviewed());
    }

    /**
     * Test canBeApproved method
     */
    public function test_can_be_approved_returns_correct_values(): void
    {
        $pendingApprovalDoc = new Document(['status' => 'pending_approval']);
        $this->assertTrue($pendingApprovalDoc->canBeApproved());

        $draftDoc = new Document(['status' => 'draft']);
        $this->assertFalse($draftDoc->canBeApproved());
    }

    /**
     * Test needsReview method
     */
    public function test_needs_review_returns_true_when_past_review_date(): void
    {
        $doc = new Document([
            'next_review_date' => now()->subDay(),
        ]);

        $this->assertTrue($doc->needsReview());
    }

    /**
     * Test needsReview returns false when no review date
     */
    public function test_needs_review_returns_false_when_no_review_date(): void
    {
        $doc = new Document([
            'next_review_date' => null,
        ]);

        $this->assertFalse($doc->needsReview());
    }

    /**
     * Test submitForReview workflow method
     */
    public function test_submit_for_review_workflow(): void
    {
        $doc = $this->createValidDocument(['status' => 'draft']);

        $result = $doc->submitForReview();

        $this->assertTrue($result);
        $this->assertEquals('pending_review', $doc->fresh()->status);
    }

    /**
     * Test submitForReview fails for non-draft
     */
    public function test_submit_for_review_fails_for_non_draft(): void
    {
        $doc = $this->createValidDocument(['status' => 'effective']);

        $result = $doc->submitForReview();

        $this->assertFalse($result);
        $this->assertEquals('effective', $doc->fresh()->status);
    }

    /**
     * Test review workflow method
     */
    public function test_review_workflow(): void
    {
        $user = User::factory()->create();
        $doc = $this->createValidDocument(['status' => 'pending_review']);

        $result = $doc->review($user->id);

        $this->assertTrue($result);
        $doc = $doc->fresh();
        $this->assertEquals('pending_approval', $doc->status);
        $this->assertEquals($user->id, $doc->reviewed_by);
        $this->assertNotNull($doc->reviewed_date);
    }

    /**
     * Test approve workflow method
     */
    public function test_approve_workflow(): void
    {
        $user = User::factory()->create();
        $doc = $this->createValidDocument(['status' => 'pending_approval']);

        $result = $doc->approve($user->id);

        $this->assertTrue($result);
        $doc = $doc->fresh();
        $this->assertEquals('approved', $doc->status);
        $this->assertEquals($user->id, $doc->approved_by);
        $this->assertNotNull($doc->approved_date);
    }

    /**
     * Test makeEffective workflow method
     */
    public function test_make_effective_workflow(): void
    {
        $doc = $this->createValidDocument(['status' => 'approved']);

        $result = $doc->makeEffective();

        $this->assertTrue($result);
        $doc = $doc->fresh();
        $this->assertEquals('effective', $doc->status);
        $this->assertNotNull($doc->effective_date);
        $this->assertNotNull($doc->next_review_date);
    }

    /**
     * Test makeEffective marks superseded document as obsolete
     */
    public function test_make_effective_marks_superseded_as_obsolete(): void
    {
        $oldDoc = $this->createValidDocument(['status' => 'effective']);

        $newDoc = $this->createValidDocument([
            'status' => 'approved',
            'supersedes_id' => $oldDoc->id,
        ]);

        $newDoc->makeEffective();

        $this->assertEquals('obsolete', $oldDoc->fresh()->status);
        $this->assertEquals('effective', $newDoc->fresh()->status);
    }

    /**
     * Test makeObsolete workflow method
     */
    public function test_make_obsolete_workflow(): void
    {
        $doc = $this->createValidDocument(['status' => 'effective']);

        $result = $doc->makeObsolete();

        $this->assertTrue($result);
        $this->assertEquals('obsolete', $doc->fresh()->status);
    }

    /**
     * Test archive workflow method
     */
    public function test_archive_workflow(): void
    {
        $doc = $this->createValidDocument(['status' => 'obsolete']);

        $result = $doc->archive();

        $this->assertTrue($result);
        $this->assertEquals('archived', $doc->fresh()->status);
    }

    /**
     * Test archive fails for non-obsolete
     */
    public function test_archive_fails_for_non_obsolete(): void
    {
        $doc = $this->createValidDocument(['status' => 'effective']);

        $result = $doc->archive();

        $this->assertFalse($result);
        $this->assertEquals('effective', $doc->fresh()->status);
    }

    /**
     * Test category label accessor
     */
    public function test_category_label_attribute(): void
    {
        $categoryLabels = [
            'quality_manual' => 'Quality Manual',
            'procedure' => 'Procedure',
            'work_instruction' => 'Work Instruction',
            'form' => 'Form',
            'record' => 'Record',
            'external_document' => 'External Document',
        ];

        foreach ($categoryLabels as $category => $expectedLabel) {
            $doc = new Document(['category' => $category]);
            $this->assertEquals($expectedLabel, $doc->category_label, "Failed for category: {$category}");
        }
    }

    /**
     * Test status color accessor
     */
    public function test_status_color_attribute(): void
    {
        $statusColors = [
            'draft' => 'secondary',
            'pending_review' => 'info',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'effective' => 'primary',
            'obsolete' => 'danger',
            'archived' => 'dark',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $doc = new Document(['status' => $status]);
            $this->assertEquals($expectedColor, $doc->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test file size formatted accessor
     */
    public function test_file_size_formatted_attribute(): void
    {
        $doc = new Document(['file_size' => 1024]);
        $this->assertEquals('1 KB', $doc->file_size_formatted);

        $doc = new Document(['file_size' => 1048576]);
        $this->assertEquals('1 MB', $doc->file_size_formatted);

        $doc = new Document(['file_size' => 500]);
        $this->assertEquals('500 B', $doc->file_size_formatted);

        $doc = new Document(['file_size' => null]);
        $this->assertEquals('N/A', $doc->file_size_formatted);
    }

    /**
     * Test days until review accessor
     */
    public function test_days_until_review_attribute(): void
    {
        $doc = new Document(['next_review_date' => now()->addDays(30)->startOfDay()]);
        $daysUntilReview = $doc->days_until_review;
        $this->assertGreaterThanOrEqual(29, $daysUntilReview);
        $this->assertLessThanOrEqual(30, $daysUntilReview);

        $doc = new Document(['next_review_date' => now()->subDays(10)]);
        $this->assertLessThan(0, $doc->days_until_review);

        $doc = new Document(['next_review_date' => null]);
        $this->assertNull($doc->days_until_review);
    }

    /**
     * Test effective scope
     */
    public function test_effective_scope(): void
    {
        $this->createValidDocument(['title' => 'Doc 1', 'status' => 'effective']);
        $this->createValidDocument(['title' => 'Doc 2', 'status' => 'effective']);
        $this->createValidDocument(['title' => 'Doc 3', 'status' => 'draft']);

        $effectiveDocs = Document::effective()->get();

        $this->assertEquals(2, $effectiveDocs->count());
    }

    /**
     * Test byCategory scope
     */
    public function test_by_category_scope(): void
    {
        $this->createValidDocument(['title' => 'Doc 1', 'category' => 'procedure']);
        $this->createValidDocument(['title' => 'Doc 2', 'category' => 'form']);
        $this->createValidDocument(['title' => 'Doc 3', 'category' => 'procedure']);

        $procedures = Document::byCategory('procedure')->get();
        $forms = Document::byCategory('form')->get();

        $this->assertEquals(2, $procedures->count());
        $this->assertEquals(1, $forms->count());
    }

    /**
     * Test needingReview scope
     */
    public function test_needing_review_scope(): void
    {
        $this->createValidDocument([
            'title' => 'Needs Review',
            'status' => 'effective',
            'next_review_date' => now()->subDay(),
        ]);
        $this->createValidDocument([
            'title' => 'Not Due Yet',
            'status' => 'effective',
            'next_review_date' => now()->addMonth(),
        ]);

        $needsReview = Document::needingReview()->get();

        $this->assertEquals(1, $needsReview->count());
        $this->assertEquals('Needs Review', $needsReview->first()->title);
    }

    /**
     * Test soft deletes
     */
    public function test_uses_soft_deletes(): void
    {
        $doc = $this->createValidDocument();
        $docId = $doc->id;
        $doc->delete();

        $this->assertNull(Document::find($docId));
        $this->assertNotNull(Document::withTrashed()->find($docId));
    }

    /**
     * Test owner relationship
     */
    public function test_owner_relationship(): void
    {
        $user = User::factory()->create();
        $doc = $this->createValidDocument(['owner_id' => $user->id]);

        $this->assertInstanceOf(User::class, $doc->owner);
        $this->assertEquals($user->id, $doc->owner->id);
    }

    /**
     * Test supersedes relationship
     */
    public function test_supersedes_relationship(): void
    {
        $oldDoc = $this->createValidDocument(['status' => 'obsolete']);
        $newDoc = $this->createValidDocument([
            'status' => 'effective',
            'supersedes_id' => $oldDoc->id,
        ]);

        $this->assertInstanceOf(Document::class, $newDoc->supersedes);
        $this->assertEquals($oldDoc->id, $newDoc->supersedes->id);
    }

    /**
     * Test supersededBy relationship
     */
    public function test_superseded_by_relationship(): void
    {
        $oldDoc = $this->createValidDocument(['status' => 'obsolete']);
        $newDoc = $this->createValidDocument([
            'status' => 'effective',
            'supersedes_id' => $oldDoc->id,
        ]);

        $this->assertCount(1, $oldDoc->supersededBy);
        $this->assertEquals($newDoc->id, $oldDoc->supersededBy->first()->id);
    }
}
