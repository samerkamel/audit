<?php

namespace App\Notifications;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Services\PdfExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?Department $department;
    protected ?\Carbon\Carbon $scheduledDate;
    protected bool $attachPdf;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AuditPlan $auditPlan,
        ?Department $department = null,
        bool $attachPdf = true
    ) {
        $this->department = $department;
        $this->attachPdf = $attachPdf;
        $this->determineScheduledDate();
    }

    /**
     * Determine the scheduled date from audit plan departments.
     */
    protected function determineScheduledDate(): void
    {
        if ($this->department) {
            // Get scheduled date for specific department
            $pivot = $this->auditPlan->departments()
                ->where('departments.id', $this->department->id)
                ->first()?->pivot;
            $this->scheduledDate = $pivot?->planned_start_date ? \Carbon\Carbon::parse($pivot->planned_start_date) : null;
        } else {
            // Get earliest scheduled date from all departments
            $earliestDate = $this->auditPlan->departments()
                ->whereNotNull('audit_plan_department.planned_start_date')
                ->orderBy('audit_plan_department.planned_start_date')
                ->first()?->pivot?->planned_start_date;
            $this->scheduledDate = $earliestDate ? \Carbon\Carbon::parse($earliestDate) : null;
        }

        // Fallback to actual_start_date if available
        if (!$this->scheduledDate && $this->auditPlan->actual_start_date) {
            $this->scheduledDate = $this->auditPlan->actual_start_date;
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Audit Scheduled: {$this->auditPlan->title}")
            ->line("You have been assigned to an upcoming audit.")
            ->line("**Audit:** {$this->auditPlan->title}")
            ->line("**Type:** {$this->auditPlan->audit_type_label}");

        if ($this->scheduledDate) {
            $mail->line("**Scheduled Date:** {$this->scheduledDate->format('F d, Y')}");
        }

        if ($this->department) {
            $mail->line("**Department:** {$this->department->name}");
        }

        // Attach PDF schedule if enabled
        if ($this->attachPdf) {
            try {
                $pdfService = app(PdfExportService::class);
                $pdfContent = $pdfService->generateAuditSchedulePdf($this->auditPlan)->output();
                $filename = $pdfService->getAuditPlanFilename($this->auditPlan);

                $mail->attachData($pdfContent, $filename, [
                    'mime' => 'application/pdf',
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the notification
                \Log::warning('Failed to attach PDF to audit notification: ' . $e->getMessage());
            }
        }

        return $mail
            ->action('View Audit', route('audit-plans.show', $this->auditPlan))
            ->line('Please prepare accordingly.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $message = $this->auditPlan->title;

        if ($this->scheduledDate) {
            $daysUntil = now()->diffInDays($this->scheduledDate, false);
            $message .= $daysUntil > 0 ? " scheduled in {$daysUntil} days" : " starting today";
        }

        if ($this->department) {
            $message .= " - {$this->department->name}";
        }

        return [
            'type' => 'audit_scheduled',
            'title' => 'Audit Scheduled',
            'message' => $message,
            'icon' => 'clipboard-check',
            'color' => 'info',
            'action_url' => route('audit-plans.show', $this->auditPlan),
            'action_text' => 'View Audit',
            'notifiable_type' => AuditPlan::class,
            'notifiable_id' => $this->auditPlan->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
