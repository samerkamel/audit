<?php

namespace App\Console\Commands;

use App\Helpers\WorkingDaysCalculator;
use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\Certificate;
use App\Models\Department;
use App\Models\Document;
use App\Models\Notification;
use App\Models\ReminderSetting;
use App\Models\Sector;
use App\Models\User;
use App\Notifications\AuditScheduledNotification;
use App\Notifications\CarDueNotification;
use App\Notifications\CarEscalationNotification;
use App\Notifications\CertificateExpiryNotification;
use App\Notifications\DocumentReviewNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled
                            {--type= : Specific notification type to send (car, certificate, document, audit)}
                            {--dry-run : Preview what notifications would be sent without actually sending them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications for due items (CARs, certificates, documents, audits)';

    /**
     * Days before due date to start sending reminders.
     */
    protected array $reminderDays = [14, 7, 3, 1, 0];

    /**
     * Days after CAR issuance without response to escalate to Sector CEO.
     */
    protected int $escalationDaysToSectorCeo = 7;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode - no notifications will be sent.');
        }

        $this->info('Checking for items requiring notifications...');

        $notificationsSent = 0;

        // Process based on type or all
        if (!$type || $type === 'car') {
            $notificationsSent += $this->processCarNotifications($dryRun);
            $notificationsSent += $this->processCarEscalations($dryRun);
        }

        if (!$type || $type === 'certificate') {
            $notificationsSent += $this->processCertificateNotifications($dryRun);
        }

        if (!$type || $type === 'document') {
            $notificationsSent += $this->processDocumentNotifications($dryRun);
        }

        if (!$type || $type === 'audit') {
            $notificationsSent += $this->processAuditNotifications($dryRun);
        }

        $this->info("Total notifications " . ($dryRun ? 'that would be ' : '') . "sent: {$notificationsSent}");

        return Command::SUCCESS;
    }

    /**
     * Process CAR due notifications.
     */
    protected function processCarNotifications(bool $dryRun): int
    {
        $this->newLine();
        $this->info('Processing CAR notifications...');

        $count = 0;

        // Get CARs with responses that have pending actions
        $cars = Car::with(['latestResponse', 'toDepartment.users'])
            ->whereIn('status', ['issued', 'in_progress'])
            ->whereHas('latestResponse')
            ->get();

        foreach ($cars as $car) {
            $response = $car->latestResponse;
            if (!$response) {
                continue;
            }

            // Check correction target date
            if ($response->correction_target_date && !$response->correction_actual_date) {
                $daysUntil = now()->diffInDays($response->correction_target_date, false);

                if ($this->shouldSendReminder($daysUntil, 'car', $car->id, 'correction')) {
                    $users = $this->getCarNotificationRecipients($car);
                    $count += $this->sendCarNotification($car, $users, 'correction', $dryRun);
                }
            }

            // Check corrective action target date
            if ($response->corrective_action_target_date && !$response->corrective_action_actual_date) {
                $daysUntil = now()->diffInDays($response->corrective_action_target_date, false);

                if ($this->shouldSendReminder($daysUntil, 'car', $car->id, 'corrective_action')) {
                    $users = $this->getCarNotificationRecipients($car);
                    $count += $this->sendCarNotification($car, $users, 'corrective_action', $dryRun);
                }
            }
        }

        $this->info("  CAR notifications: {$count}");

        return $count;
    }

    /**
     * Process certificate expiry notifications.
     */
    protected function processCertificateNotifications(bool $dryRun): int
    {
        $this->newLine();
        $this->info('Processing certificate expiry notifications...');

        $count = 0;

        // Get certificates expiring within 90 days or already expired
        $certificates = Certificate::where('status', 'valid')
            ->where('expiry_date', '<=', now()->addDays(90))
            ->get();

        foreach ($certificates as $certificate) {
            $daysUntil = now()->diffInDays($certificate->expiry_date, false);

            // Send for specific reminder days or if overdue
            if ($this->shouldSendReminder($daysUntil, 'certificate', $certificate->id)) {
                // Notify admins and quality managers
                $users = User::where('is_active', true)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['admin', 'quality_manager', 'management_representative']);
                    })
                    ->get();

                foreach ($users as $user) {
                    if (!$dryRun) {
                        $this->createNotification($user, $certificate, 'certificate_expiry');
                        $user->notify(new CertificateExpiryNotification($certificate));
                    }
                    $count++;
                    $this->line("  - Certificate {$certificate->certificate_number} → {$user->name}");
                }
            }
        }

        $this->info("  Certificate notifications: {$count}");

        return $count;
    }

    /**
     * Process document review notifications.
     */
    protected function processDocumentNotifications(bool $dryRun): int
    {
        $this->newLine();
        $this->info('Processing document review notifications...');

        $count = 0;

        // Get documents due for review
        $documents = Document::with('owner')
            ->where('status', 'effective')
            ->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now()->addDays(30))
            ->get();

        foreach ($documents as $document) {
            $daysUntil = now()->diffInDays($document->next_review_date, false);

            if ($this->shouldSendReminder($daysUntil, 'document', $document->id)) {
                // Notify document owner
                if ($document->owner) {
                    if (!$dryRun) {
                        $this->createNotification($document->owner, $document, 'document_review');
                        $document->owner->notify(new DocumentReviewNotification($document));
                    }
                    $count++;
                    $this->line("  - Document {$document->document_number} → {$document->owner->name}");
                }
            }
        }

        $this->info("  Document notifications: {$count}");

        return $count;
    }

    /**
     * Process audit scheduled notifications.
     */
    protected function processAuditNotifications(bool $dryRun): int
    {
        $this->newLine();
        $this->info('Processing audit notifications...');

        $count = 0;

        // Get audit plans with departments scheduled within 14 days
        $auditPlans = AuditPlan::with(['departments', 'leadAuditor'])
            ->whereIn('status', ['planned', 'scheduled'])
            ->whereHas('departments', function ($q) {
                $q->where('audit_plan_department.planned_start_date', '>=', now())
                    ->where('audit_plan_department.planned_start_date', '<=', now()->addDays(14));
            })
            ->get();

        foreach ($auditPlans as $auditPlan) {
            foreach ($auditPlan->departments as $department) {
                if (!$department->pivot->planned_start_date) {
                    continue;
                }

                $startDate = \Carbon\Carbon::parse($department->pivot->planned_start_date);
                $daysUntil = now()->diffInDays($startDate, false);

                if ($this->shouldSendReminder($daysUntil, 'audit', "{$auditPlan->id}-{$department->id}")) {
                    // Notify lead auditor
                    if ($auditPlan->leadAuditor) {
                        if (!$dryRun) {
                            $auditPlan->leadAuditor->notify(new AuditScheduledNotification($auditPlan, $department));
                        }
                        $count++;
                        $this->line("  - Audit {$auditPlan->title} ({$department->name}) → {$auditPlan->leadAuditor->name}");
                    }

                    // Notify department users
                    $departmentUsers = User::where('department_id', $department->id)
                        ->where('is_active', true)
                        ->get();

                    foreach ($departmentUsers as $user) {
                        if (!$dryRun) {
                            $user->notify(new AuditScheduledNotification($auditPlan, $department));
                        }
                        $count++;
                        $this->line("  - Audit {$auditPlan->title} ({$department->name}) → {$user->name}");
                    }
                }
            }
        }

        $this->info("  Audit notifications: {$count}");

        return $count;
    }

    /**
     * Process CAR escalation notifications.
     * Escalate to Sector CEO after 1 week without response.
     */
    protected function processCarEscalations(bool $dryRun): int
    {
        $this->newLine();
        $this->info('Processing CAR escalations...');

        $count = 0;
        $workingDays = app(WorkingDaysCalculator::class);

        // Get escalation settings from reminder_settings if available
        $escalationSetting = ReminderSetting::where('event_type', 'car_escalation')
            ->where('is_active', true)
            ->first();

        $escalationDays = $escalationSetting
            ? $escalationSetting->days_before
            : $this->escalationDaysToSectorCeo;

        // Get CARs that are issued but have no response after X days
        $cars = Car::with(['toDepartment.sector', 'latestResponse'])
            ->where('status', 'issued')
            ->where('issued_date', '<=', now()->subDays($escalationDays))
            ->get();

        foreach ($cars as $car) {
            // Skip if there's already a response
            if ($car->latestResponse) {
                continue;
            }

            // Calculate working days since issuance
            $workingDaysSinceIssuance = $workingDays->diffInWorkingDays($car->issued_date, now());

            if ($workingDaysSinceIssuance >= $escalationDays) {
                // Check if we haven't already escalated recently (within 7 days)
                if ($this->hasRecentNotification('car_escalation', $car->id, 'sector_ceo', 7)) {
                    continue;
                }

                // Get Sector CEO
                $sectorCeo = $this->getSectorCeo($car);

                if ($sectorCeo) {
                    if (!$dryRun) {
                        $sectorCeo->notify(new CarEscalationNotification($car, 'sector_ceo', $workingDaysSinceIssuance));
                        $this->markNotificationSent('car_escalation', $car->id, 'sector_ceo', 7);
                    }
                    $count++;
                    $this->line("  - CAR {$car->car_number} escalated to Sector CEO: {$sectorCeo->name} (no response for {$workingDaysSinceIssuance} working days)");
                }

                // Also notify General Manager if available
                $generalManager = $this->getGeneralManager($car);
                if ($generalManager && (!$sectorCeo || $generalManager->id !== $sectorCeo->id)) {
                    if (!$dryRun) {
                        $generalManager->notify(new CarEscalationNotification($car, 'general_manager', $workingDaysSinceIssuance));
                    }
                    $count++;
                    $this->line("  - CAR {$car->car_number} escalated to General Manager: {$generalManager->name}");
                }
            }
        }

        $this->info("  CAR escalations: {$count}");

        return $count;
    }

    /**
     * Get the Sector CEO for a CAR based on the responsible department.
     */
    protected function getSectorCeo(Car $car): ?User
    {
        if (!$car->toDepartment || !$car->toDepartment->sector) {
            return null;
        }

        $sector = $car->toDepartment->sector;

        // Check if sector has a CEO/head defined
        if ($sector->ceo_id) {
            return User::find($sector->ceo_id);
        }

        // Fallback: get users with sector_head role in this sector
        return User::where('is_active', true)
            ->where('sector_id', $sector->id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Sector CEO', 'Sector Head', 'sector_ceo', 'sector_head']);
            })
            ->first();
    }

    /**
     * Get the General Manager for a CAR based on the responsible department.
     */
    protected function getGeneralManager(Car $car): ?User
    {
        if (!$car->toDepartment || !$car->toDepartment->general_manager_id) {
            return null;
        }

        return User::find($car->toDepartment->general_manager_id);
    }

    /**
     * Determine if a reminder should be sent based on days until due.
     */
    protected function shouldSendReminder(int $daysUntil, string $type, $itemId, ?string $subType = null): bool
    {
        // Always send if overdue (negative days)
        if ($daysUntil < 0) {
            // Check if we haven't sent an overdue notification recently (within 7 days)
            return !$this->hasRecentNotification($type, $itemId, $subType, 7);
        }

        // Send on specific reminder days
        if (in_array($daysUntil, $this->reminderDays)) {
            // Check if we haven't already sent for this day
            return !$this->hasRecentNotification($type, $itemId, $subType, 1);
        }

        return false;
    }

    /**
     * Check if a notification was recently sent.
     */
    protected function hasRecentNotification(string $type, $itemId, ?string $subType = null, int $withinDays = 1): bool
    {
        $cacheKey = "notification_sent:{$type}:{$itemId}" . ($subType ? ":{$subType}" : '');

        // Use cache to track sent notifications
        return cache()->has($cacheKey);
    }

    /**
     * Mark notification as sent.
     */
    protected function markNotificationSent(string $type, $itemId, ?string $subType = null, int $daysToCache = 1): void
    {
        $cacheKey = "notification_sent:{$type}:{$itemId}" . ($subType ? ":{$subType}" : '');
        cache()->put($cacheKey, true, now()->addDays($daysToCache));
    }

    /**
     * Get users who should receive CAR notifications.
     */
    protected function getCarNotificationRecipients(Car $car): \Illuminate\Support\Collection
    {
        $users = collect();

        // Add users from the responsible department
        if ($car->toDepartment) {
            $departmentUsers = User::where('department_id', $car->to_department_id)
                ->where('is_active', true)
                ->get();
            $users = $users->merge($departmentUsers);
        }

        // Add the person who issued the CAR
        if ($car->issued_by) {
            $issuer = User::find($car->issued_by);
            if ($issuer && $issuer->is_active) {
                $users->push($issuer);
            }
        }

        return $users->unique('id');
    }

    /**
     * Send CAR notification to users.
     */
    protected function sendCarNotification(Car $car, $users, string $dueType, bool $dryRun): int
    {
        $count = 0;

        foreach ($users as $user) {
            if (!$dryRun) {
                $this->createNotification($user, $car, 'car_due');
                $user->notify(new CarDueNotification($car, $dueType));
                $this->markNotificationSent('car', $car->id, $dueType);
            }
            $count++;
            $this->line("  - CAR {$car->car_number} ({$dueType}) → {$user->name}");
        }

        return $count;
    }

    /**
     * Create a database notification record.
     */
    protected function createNotification(User $user, $model, string $type): void
    {
        // The Laravel notification system handles this via toDatabase method
        // This is here for any additional custom tracking if needed
        Log::info("Notification sent", [
            'user_id' => $user->id,
            'type' => $type,
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]);
    }
}
