<?php

namespace Tests\Unit\Models;

use App\Models\Certificate;
use App\Models\ExternalAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a valid Certificate with all required fields.
     */
    private function createValidCertificate(array $attributes = []): Certificate
    {
        static $counter = 0;
        $counter++;

        $defaults = [
            'certificate_number' => 'CERT-' . date('Y') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT),
            'standard' => 'ISO 9001:2015',
            'certification_body' => 'BSI',
            'issue_date' => now()->subMonth(),
            'expiry_date' => now()->addYears(3),
            'status' => 'valid',
            'scope_of_certification' => 'Manufacturing and Quality Control',
        ];

        return Certificate::create(array_merge($defaults, $attributes));
    }

    /**
     * Create a valid ExternalAudit with all required fields.
     */
    private function createValidAudit(array $attributes = []): ExternalAudit
    {
        $defaults = [
            'audit_number' => ExternalAudit::generateAuditNumber(),
            'audit_type' => 'surveillance',
            'certification_body' => 'BSI',
            'standard' => 'ISO 9001:2015',
            'lead_auditor_name' => 'John Doe',
            'scheduled_start_date' => now()->subDays(7),
            'scheduled_end_date' => now()->subDays(5),
            'status' => 'completed',
            'result' => 'passed',
        ];

        return ExternalAudit::create(array_merge($defaults, $attributes));
    }

    /**
     * Test status color accessor returns correct values
     */
    public function test_status_color_attribute_returns_correct_colors(): void
    {
        $statusColors = [
            'valid' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            'suspended' => 'warning',
            'revoked' => 'danger',
        ];

        foreach ($statusColors as $status => $expectedColor) {
            $cert = new Certificate(['status' => $status]);
            $this->assertEquals($expectedColor, $cert->status_color, "Failed for status: {$status}");
        }
    }

    /**
     * Test certificate type label accessor
     */
    public function test_certificate_type_label_attribute(): void
    {
        $typeLabels = [
            'initial' => 'Initial Certification',
            'renewal' => 'Renewal',
            'transfer' => 'Transfer',
        ];

        foreach ($typeLabels as $type => $expectedLabel) {
            $cert = new Certificate(['certificate_type' => $type]);
            $this->assertEquals($expectedLabel, $cert->certificate_type_label, "Failed for type: {$type}");
        }
    }

    /**
     * Test days until expiry calculation
     */
    public function test_days_until_expiry_attribute(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->addDays(30)->startOfDay(),
        ]);

        $daysUntilExpiry = $cert->days_until_expiry;
        $this->assertGreaterThanOrEqual(29, $daysUntilExpiry);
        $this->assertLessThanOrEqual(30, $daysUntilExpiry);
    }

    /**
     * Test negative days until expiry for expired cert
     */
    public function test_days_until_expiry_negative_when_expired(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->subDays(10),
        ]);

        $daysUntilExpiry = $cert->days_until_expiry;
        $this->assertLessThan(0, $daysUntilExpiry);
    }

    /**
     * Test validity period in years calculation
     */
    public function test_validity_period_in_years_attribute(): void
    {
        $cert = new Certificate([
            'issue_date' => now(),
            'expiry_date' => now()->addYears(3),
        ]);

        $validityPeriod = $cert->validity_period_in_years;
        $this->assertEquals(3.0, $validityPeriod);
    }

    /**
     * Test isExpired method when certificate is expired
     */
    public function test_is_expired_returns_true_when_past_expiry(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->subDay(),
        ]);

        $this->assertTrue($cert->isExpired());
    }

    /**
     * Test isExpired method when certificate is valid
     */
    public function test_is_expired_returns_false_when_not_expired(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertFalse($cert->isExpired());
    }

    /**
     * Test isExpiringSoon returns true within 90 days
     */
    public function test_is_expiring_soon_returns_true_within_90_days(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->addDays(60),
        ]);

        $this->assertTrue($cert->isExpiringSoon());
    }

    /**
     * Test isExpiringSoon returns false when more than 90 days
     */
    public function test_is_expiring_soon_returns_false_when_over_90_days(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->addDays(100),
        ]);

        $this->assertFalse($cert->isExpiringSoon());
    }

    /**
     * Test isExpiringSoon returns false when already expired
     */
    public function test_is_expiring_soon_returns_false_when_expired(): void
    {
        $cert = new Certificate([
            'expiry_date' => now()->subDay(),
        ]);

        $this->assertFalse($cert->isExpiringSoon());
    }

    /**
     * Test isValid method returns true for valid certificate
     */
    public function test_is_valid_returns_true_for_valid_certificate(): void
    {
        $cert = new Certificate([
            'status' => 'valid',
            'issue_date' => now()->subMonth(),
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertTrue($cert->isValid());
    }

    /**
     * Test isValid returns false when status is not valid
     */
    public function test_is_valid_returns_false_when_status_not_valid(): void
    {
        $cert = new Certificate([
            'status' => 'expired',
            'issue_date' => now()->subMonth(),
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertFalse($cert->isValid());
    }

    /**
     * Test isValid returns false when not yet issued
     */
    public function test_is_valid_returns_false_when_not_yet_issued(): void
    {
        $cert = new Certificate([
            'status' => 'valid',
            'issue_date' => now()->addDay(),
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertFalse($cert->isValid());
    }

    /**
     * Test updateStatus method marks expired certificates
     */
    public function test_update_status_marks_expired_certificates(): void
    {
        $cert = $this->createValidCertificate([
            'issue_date' => now()->subYears(3)->subDay(),
            'expiry_date' => now()->subDay(),
            'status' => 'valid',
        ]);

        $cert->updateStatus();

        $this->assertEquals('expired', $cert->fresh()->status);
    }

    /**
     * Test updateStatus method marks expiring soon certificates
     */
    public function test_update_status_marks_expiring_soon_certificates(): void
    {
        $cert = $this->createValidCertificate([
            'issue_date' => now()->subYears(2),
            'expiry_date' => now()->addDays(60),
            'status' => 'valid',
        ]);

        $cert->updateStatus();

        $this->assertEquals('expiring_soon', $cert->fresh()->status);
    }

    /**
     * Test updateStatus resets to valid when no longer expiring soon
     */
    public function test_update_status_resets_to_valid_when_no_longer_expiring_soon(): void
    {
        $cert = $this->createValidCertificate([
            'issue_date' => now()->subYear(),
            'expiry_date' => now()->addDays(120),
            'status' => 'expiring_soon',
        ]);

        $cert->updateStatus();

        $this->assertEquals('valid', $cert->fresh()->status);
    }

    /**
     * Test issuedForAudit relationship
     */
    public function test_issued_for_audit_relationship(): void
    {
        $audit = $this->createValidAudit();
        $cert = $this->createValidCertificate(['issued_for_audit_id' => $audit->id]);

        $this->assertInstanceOf(ExternalAudit::class, $cert->issuedForAudit);
        $this->assertEquals($audit->id, $cert->issuedForAudit->id);
    }

    /**
     * Test createdBy relationship
     */
    public function test_created_by_relationship(): void
    {
        $user = User::factory()->create();
        $cert = $this->createValidCertificate(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $cert->createdBy);
        $this->assertEquals($user->id, $cert->createdBy->id);
    }

    /**
     * Test array casts for JSON fields
     */
    public function test_json_fields_are_cast_to_arrays(): void
    {
        $cert = $this->createValidCertificate([
            'covered_sites' => ['Site A', 'Site B'],
            'covered_processes' => ['Manufacturing', 'Quality'],
            'attachments' => ['cert.pdf'],
        ]);

        $cert = $cert->fresh();

        $this->assertIsArray($cert->covered_sites);
        $this->assertIsArray($cert->covered_processes);
        $this->assertIsArray($cert->attachments);
        $this->assertCount(2, $cert->covered_sites);
    }

    /**
     * Test soft deletes
     */
    public function test_uses_soft_deletes(): void
    {
        $cert = $this->createValidCertificate();
        $certId = $cert->id;
        $cert->delete();

        $this->assertNull(Certificate::find($certId));
        $this->assertNotNull(Certificate::withTrashed()->find($certId));
    }

    /**
     * Test date casting
     */
    public function test_dates_are_cast_correctly(): void
    {
        $cert = new Certificate([
            'issue_date' => '2025-01-01',
            'expiry_date' => '2028-01-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $cert->issue_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $cert->expiry_date);
    }

    /**
     * Test unknown status returns default color
     */
    public function test_unknown_status_returns_secondary_color(): void
    {
        $cert = new Certificate(['status' => 'unknown']);
        $this->assertEquals('secondary', $cert->status_color);
    }

    /**
     * Test unknown certificate type returns ucfirst formatted label
     */
    public function test_unknown_certificate_type_returns_ucfirst_label(): void
    {
        $cert = new Certificate(['certificate_type' => 'special']);
        $this->assertEquals('Special', $cert->certificate_type_label);
    }
}
