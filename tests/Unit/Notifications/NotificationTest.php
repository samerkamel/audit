<?php

namespace Tests\Unit\Notifications;

use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\CarResponse;
use App\Models\Certificate;
use App\Models\CustomerComplaint;
use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use App\Notifications\AuditScheduledNotification;
use App\Notifications\CarDueNotification;
use App\Notifications\CertificateExpiryNotification;
use App\Notifications\ComplaintAssignedNotification;
use App\Notifications\DocumentReviewNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test CAR due notification creation
     */
    public function test_car_due_notification_can_be_created(): void
    {
        $car = Car::factory()->create([
            'to_department_id' => $this->department->id,
            'status' => 'in_progress',
        ]);

        // Create a response with target dates
        CarResponse::create([
            'car_id' => $car->id,
            'root_cause' => 'Test root cause',
            'correction' => 'Test correction',
            'correction_target_date' => now()->addDays(5),
            'corrective_action' => 'Test corrective action',
            'corrective_action_target_date' => now()->addDays(14),
            'responded_by' => $this->user->id,
            'response_status' => 'submitted',
        ]);

        $car->refresh();
        $car->load('latestResponse');

        $notification = new CarDueNotification($car);

        $mailMessage = $notification->toMail($this->user);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertNotNull($mailMessage);
        $this->assertEquals('car_due', $databaseData['type']);
        $this->assertStringContainsString($car->car_number, $databaseData['message']);
    }

    /**
     * Test CAR due notification without response
     */
    public function test_car_due_notification_handles_missing_response(): void
    {
        $car = Car::factory()->create([
            'to_department_id' => $this->department->id,
            'status' => 'in_progress',
        ]);

        $notification = new CarDueNotification($car);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertEquals('car_due', $databaseData['type']);
        $this->assertEquals('CAR Requires Action', $databaseData['title']);
    }

    /**
     * Test certificate expiry notification
     */
    public function test_certificate_expiry_notification_can_be_created(): void
    {
        $certificate = Certificate::factory()->create([
            'expiry_date' => now()->addDays(30),
            'status' => 'valid',
        ]);

        $notification = new CertificateExpiryNotification($certificate);

        $mailMessage = $notification->toMail($this->user);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertNotNull($mailMessage);
        $this->assertEquals('certificate_expiry', $databaseData['type']);
        $this->assertEquals('Certificate Expiring Soon', $databaseData['title']);
        $this->assertStringContainsString($certificate->certificate_number, $databaseData['message']);
    }

    /**
     * Test certificate expired notification
     */
    public function test_certificate_expired_notification_shows_danger_color(): void
    {
        $certificate = Certificate::factory()->create([
            'expiry_date' => now()->subDays(5),
            'status' => 'valid',
        ]);

        $notification = new CertificateExpiryNotification($certificate);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertEquals('Certificate Expired', $databaseData['title']);
        $this->assertEquals('danger', $databaseData['color']);
    }

    /**
     * Test audit scheduled notification
     */
    public function test_audit_scheduled_notification_can_be_created(): void
    {
        $auditPlan = AuditPlan::factory()->create([
            'lead_auditor_id' => $this->user->id,
            'status' => 'planned',
        ]);

        // Attach department with planned dates
        $auditPlan->departments()->attach($this->department->id, [
            'planned_start_date' => now()->addDays(7),
            'planned_end_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        $notification = new AuditScheduledNotification($auditPlan);

        $mailMessage = $notification->toMail($this->user);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertNotNull($mailMessage);
        $this->assertEquals('audit_scheduled', $databaseData['type']);
        $this->assertEquals('Audit Scheduled', $databaseData['title']);
        $this->assertStringContainsString($auditPlan->title, $databaseData['message']);
    }

    /**
     * Test audit scheduled notification with department
     */
    public function test_audit_scheduled_notification_with_department(): void
    {
        $auditPlan = AuditPlan::factory()->create([
            'lead_auditor_id' => $this->user->id,
            'status' => 'planned',
        ]);

        $auditPlan->departments()->attach($this->department->id, [
            'planned_start_date' => now()->addDays(7),
            'planned_end_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        $notification = new AuditScheduledNotification($auditPlan, $this->department);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertStringContainsString($this->department->name, $databaseData['message']);
    }

    /**
     * Test complaint assigned notification
     */
    public function test_complaint_assigned_notification_can_be_created(): void
    {
        $complaint = CustomerComplaint::factory()->create([
            'assigned_to_user_id' => $this->user->id,
            'assigned_to_department_id' => $this->department->id,
            'priority' => 'high',
            'status' => 'new',
        ]);

        $notification = new ComplaintAssignedNotification($complaint);

        $mailMessage = $notification->toMail($this->user);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertNotNull($mailMessage);
        $this->assertEquals('complaint_assigned', $databaseData['type']);
        $this->assertEquals('Complaint Assigned', $databaseData['title']);
        $this->assertEquals('danger', $databaseData['color']); // High priority
    }

    /**
     * Test document review notification
     */
    public function test_document_review_notification_can_be_created(): void
    {
        $document = Document::factory()->create([
            'owner_id' => $this->user->id,
            'next_review_date' => now()->addDays(7),
            'status' => 'effective',
        ]);

        $notification = new DocumentReviewNotification($document);

        $mailMessage = $notification->toMail($this->user);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertNotNull($mailMessage);
        $this->assertEquals('document_review', $databaseData['type']);
        $this->assertEquals('Document Review Due', $databaseData['title']);
    }

    /**
     * Test document overdue notification
     */
    public function test_document_overdue_notification_shows_danger_color(): void
    {
        $document = Document::factory()->create([
            'owner_id' => $this->user->id,
            'next_review_date' => now()->subDays(5),
            'status' => 'effective',
        ]);

        $notification = new DocumentReviewNotification($document);
        $databaseData = $notification->toDatabase($this->user);

        $this->assertEquals('Document Review Overdue', $databaseData['title']);
        $this->assertEquals('danger', $databaseData['color']);
    }

    /**
     * Test notification can be sent via database channel
     */
    public function test_notifications_use_database_channel(): void
    {
        $certificate = Certificate::factory()->create([
            'expiry_date' => now()->addDays(30),
            'status' => 'valid',
        ]);

        $notification = new CertificateExpiryNotification($certificate);
        $channels = $notification->via($this->user);

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
    }

    /**
     * Test notification can be sent to user
     */
    public function test_notification_can_be_sent_to_user(): void
    {
        Notification::fake();

        $certificate = Certificate::factory()->create([
            'expiry_date' => now()->addDays(30),
            'status' => 'valid',
        ]);

        $this->user->notify(new CertificateExpiryNotification($certificate));

        Notification::assertSentTo(
            $this->user,
            CertificateExpiryNotification::class
        );
    }
}
