<?php

namespace App\Console\Commands;

use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\Certificate;
use App\Models\Document;
use App\Models\ExternalAudit;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:send {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send reminder notifications for upcoming events (audits, CARs, certificates, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No notifications will actually be sent');
        }

        $this->info('Checking for reminders to send...');

        $settings = ReminderSetting::active()->get();
        $totalSent = 0;

        foreach ($settings as $setting) {
            $count = $this->processReminderSetting($setting, $dryRun);
            $totalSent += $count;
        }

        $this->info("Finished. Total reminders sent: {$totalSent}");

        return self::SUCCESS;
    }

    /**
     * Process a single reminder setting.
     */
    protected function processReminderSetting(ReminderSetting $setting, bool $dryRun): int
    {
        $count = 0;

        foreach ($setting->intervals as $intervalHours) {
            $count += match ($setting->entity_type) {
                'audit_plan' => $this->processAuditPlans($setting, $intervalHours, $dryRun),
                'external_audit' => $this->processExternalAudits($setting, $intervalHours, $dryRun),
                'car' => $this->processCars($setting, $intervalHours, $dryRun),
                'certificate' => $this->processCertificates($setting, $intervalHours, $dryRun),
                'document' => $this->processDocuments($setting, $intervalHours, $dryRun),
                default => 0,
            };
        }

        return $count;
    }

    /**
     * Process audit plan reminders.
     */
    protected function processAuditPlans(ReminderSetting $setting, int $intervalHours, bool $dryRun): int
    {
        $dateField = $this->getDateField($setting->event_type);
        if (!$dateField) {
            return 0;
        }

        $targetTime = now()->addHours($intervalHours);
        $windowStart = $targetTime->copy()->subMinutes(30);
        $windowEnd = $targetTime->copy()->addMinutes(30);

        $audits = AuditPlan::whereNotNull($dateField)
            ->whereBetween($dateField, [$windowStart, $windowEnd])
            ->whereIn('status', ['planned', 'in_progress'])
            ->get();

        $count = 0;
        foreach ($audits as $audit) {
            $users = $this->getAuditPlanNotifyUsers($audit);
            foreach ($users as $user) {
                if ($this->sendReminder($setting, $audit, $user, $intervalHours, $dryRun)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Process external audit reminders.
     */
    protected function processExternalAudits(ReminderSetting $setting, int $intervalHours, bool $dryRun): int
    {
        $dateField = match ($setting->event_type) {
            'start_date' => 'scheduled_start_date',
            'end_date' => 'scheduled_end_date',
            default => null,
        };

        if (!$dateField) {
            return 0;
        }

        $targetTime = now()->addHours($intervalHours);
        $windowStart = $targetTime->copy()->subMinutes(30);
        $windowEnd = $targetTime->copy()->addMinutes(30);

        $audits = ExternalAudit::whereNotNull($dateField)
            ->whereBetween($dateField, [$windowStart, $windowEnd])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->get();

        $count = 0;
        foreach ($audits as $audit) {
            $users = $this->getExternalAuditNotifyUsers($audit);
            foreach ($users as $user) {
                if ($this->sendReminder($setting, $audit, $user, $intervalHours, $dryRun)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Process CAR reminders.
     */
    protected function processCars(ReminderSetting $setting, int $intervalHours, bool $dryRun): int
    {
        $dateField = match ($setting->event_type) {
            'due_date' => 'due_date',
            default => null,
        };

        if (!$dateField) {
            return 0;
        }

        $targetTime = now()->addHours($intervalHours);
        $windowStart = $targetTime->copy()->subMinutes(30);
        $windowEnd = $targetTime->copy()->addMinutes(30);

        $cars = Car::whereNotNull($dateField)
            ->whereBetween($dateField, [$windowStart, $windowEnd])
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->get();

        $count = 0;
        foreach ($cars as $car) {
            $users = $this->getCarNotifyUsers($car);
            foreach ($users as $user) {
                if ($this->sendReminder($setting, $car, $user, $intervalHours, $dryRun)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Process certificate reminders.
     */
    protected function processCertificates(ReminderSetting $setting, int $intervalHours, bool $dryRun): int
    {
        $dateField = match ($setting->event_type) {
            'expiry_date' => 'expiry_date',
            default => null,
        };

        if (!$dateField) {
            return 0;
        }

        $targetTime = now()->addHours($intervalHours);
        $windowStart = $targetTime->copy()->subMinutes(30);
        $windowEnd = $targetTime->copy()->addMinutes(30);

        $certificates = Certificate::whereNotNull($dateField)
            ->whereBetween($dateField, [$windowStart, $windowEnd])
            ->whereIn('status', ['valid', 'expiring_soon'])
            ->get();

        $count = 0;
        foreach ($certificates as $certificate) {
            $users = $this->getCertificateNotifyUsers();
            foreach ($users as $user) {
                if ($this->sendReminder($setting, $certificate, $user, $intervalHours, $dryRun)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Process document reminders.
     */
    protected function processDocuments(ReminderSetting $setting, int $intervalHours, bool $dryRun): int
    {
        $dateField = match ($setting->event_type) {
            'review_date' => 'next_review_date',
            default => null,
        };

        if (!$dateField) {
            return 0;
        }

        $targetTime = now()->addHours($intervalHours);
        $windowStart = $targetTime->copy()->subMinutes(30);
        $windowEnd = $targetTime->copy()->addMinutes(30);

        $documents = Document::whereNotNull($dateField)
            ->whereBetween($dateField, [$windowStart, $windowEnd])
            ->where('status', 'effective')
            ->get();

        $count = 0;
        foreach ($documents as $document) {
            $users = $this->getDocumentNotifyUsers($document);
            foreach ($users as $user) {
                if ($this->sendReminder($setting, $document, $user, $intervalHours, $dryRun)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Send a reminder notification.
     */
    protected function sendReminder(ReminderSetting $setting, $entity, User $user, int $intervalHours, bool $dryRun): bool
    {
        $entityType = $setting->entity_type;
        $eventType = $setting->event_type;

        // Check if already sent
        if (ReminderSetting::wasReminderSent($entityType, $entity->id, $eventType, $intervalHours, $user->id)) {
            return false;
        }

        $intervalLabel = ReminderSetting::getIntervalOptions()[$intervalHours] ?? "{$intervalHours} hours";

        if ($dryRun) {
            $this->line("  Would send: {$setting->name} ({$intervalLabel}) to {$user->email} for {$entityType} #{$entity->id}");
            return true;
        }

        try {
            $user->notify(new ReminderNotification($setting, $entity, $intervalHours));

            // Mark as sent
            ReminderSetting::markReminderSent($entityType, $entity->id, $eventType, $intervalHours, $user->id);

            $this->line("  Sent: {$setting->name} ({$intervalLabel}) to {$user->email}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send reminder notification", [
                'setting_id' => $setting->id,
                'entity_type' => $entityType,
                'entity_id' => $entity->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("  Failed: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Get the date field for an event type.
     */
    protected function getDateField(string $eventType): ?string
    {
        return match ($eventType) {
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'due_date' => 'due_date',
            'expiry_date' => 'expiry_date',
            'review_date' => 'next_review_date',
            default => null,
        };
    }

    /**
     * Get users to notify for an audit plan.
     */
    protected function getAuditPlanNotifyUsers(AuditPlan $audit): array
    {
        $users = [];

        // Lead auditor
        if ($audit->leadAuditor) {
            $users[] = $audit->leadAuditor;
        }

        // Quality managers
        $qualityManagers = User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['Quality Manager', 'Admin']))
            ->get();

        return array_merge($users, $qualityManagers->all());
    }

    /**
     * Get users to notify for an external audit.
     */
    protected function getExternalAuditNotifyUsers(ExternalAudit $audit): array
    {
        $users = [];

        // Coordinator
        if ($audit->coordinator) {
            $users[] = $audit->coordinator;
        }

        // Quality managers
        $qualityManagers = User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['Quality Manager', 'Admin']))
            ->get();

        return array_merge($users, $qualityManagers->all());
    }

    /**
     * Get users to notify for a CAR.
     */
    protected function getCarNotifyUsers(Car $car): array
    {
        $users = [];

        // Department head of the responsible department
        if ($car->toDepartment && $car->toDepartment->head) {
            $users[] = $car->toDepartment->head;
        }

        // Issuer
        if ($car->issuedBy) {
            $users[] = $car->issuedBy;
        }

        return $users;
    }

    /**
     * Get users to notify for certificates.
     */
    protected function getCertificateNotifyUsers(): array
    {
        return User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->whereIn('name', ['Quality Manager', 'Admin']))
            ->get()
            ->all();
    }

    /**
     * Get users to notify for a document.
     */
    protected function getDocumentNotifyUsers(Document $document): array
    {
        $users = [];

        // Document owner
        if ($document->owner) {
            $users[] = $document->owner;
        }

        return $users;
    }
}
