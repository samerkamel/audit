<?php

namespace App\Notifications;

use App\Models\AuditExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AuditExecution $auditExecution
    ) {
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
        return (new MailMessage)
            ->subject("Audit Scheduled: {$this->auditExecution->auditPlan->title}")
            ->line("You have been assigned to an upcoming audit.")
            ->line("**Audit:** {$this->auditExecution->auditPlan->title}")
            ->line("**Scheduled Date:** {$this->auditExecution->scheduled_date->format('F d, Y')}")
            ->line("**Department:** {$this->auditExecution->department->name}")
            ->line("**Type:** {$this->auditExecution->auditPlan->audit_type}")
            ->action('View Audit', route('audit-execution.show', $this->auditExecution))
            ->line('Please prepare accordingly.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysUntil = now()->diffInDays($this->auditExecution->scheduled_date, false);

        return [
            'type' => 'audit_scheduled',
            'title' => 'Audit Scheduled',
            'message' => "{$this->auditExecution->auditPlan->title} scheduled in {$daysUntil} days",
            'icon' => 'clipboard-check',
            'color' => 'info',
            'action_url' => route('audit-execution.show', $this->auditExecution),
            'action_text' => 'View Audit',
            'notifiable_type' => AuditExecution::class,
            'notifiable_id' => $this->auditExecution->id,
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
