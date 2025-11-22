<?php

namespace App\Notifications;

use App\Models\ExternalAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExternalAuditNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ExternalAudit $audit;
    protected string $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(ExternalAudit $audit, string $action)
    {
        $this->audit = $audit;
        $this->action = $action;
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
        $message = (new MailMessage)
            ->greeting("Hello {$notifiable->name},");

        switch ($this->action) {
            case 'scheduled':
                $message
                    ->subject("External Audit Scheduled: {$this->audit->audit_number}")
                    ->line("A new external audit has been scheduled.")
                    ->line("Audit Number: {$this->audit->audit_number}")
                    ->line("Certification Body: {$this->audit->certification_body}")
                    ->line("Standard: {$this->audit->standard}")
                    ->line("Scheduled Date: " . $this->audit->scheduled_start_date->format('M d, Y') . ' - ' . $this->audit->scheduled_end_date->format('M d, Y'))
                    ->line("Lead Auditor: {$this->audit->lead_auditor_name}");
                break;

            case 'started':
                $message
                    ->subject("External Audit Started: {$this->audit->audit_number}")
                    ->line("An external audit has started.")
                    ->line("Audit Number: {$this->audit->audit_number}")
                    ->line("Certification Body: {$this->audit->certification_body}")
                    ->line("Started on: " . $this->audit->actual_start_date->format('M d, Y'));
                break;

            case 'completed':
                $message
                    ->subject("External Audit Completed: {$this->audit->audit_number}")
                    ->line("An external audit has been completed.")
                    ->line("Audit Number: {$this->audit->audit_number}")
                    ->line("Result: " . ucfirst($this->audit->result))
                    ->line("Major NCRs: {$this->audit->major_ncrs_count}")
                    ->line("Minor NCRs: {$this->audit->minor_ncrs_count}");
                break;

            case 'cancelled':
                $message
                    ->subject("External Audit Cancelled: {$this->audit->audit_number}")
                    ->line("An external audit has been cancelled.")
                    ->line("Audit Number: {$this->audit->audit_number}")
                    ->line("Certification Body: {$this->audit->certification_body}");
                break;

            default:
                $message
                    ->subject("External Audit Update: {$this->audit->audit_number}")
                    ->line("An external audit has been updated.")
                    ->line("Audit Number: {$this->audit->audit_number}");
        }

        return $message
            ->action('View Audit Details', route('external-audits.show', $this->audit))
            ->line('Thank you for your attention.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $title = match ($this->action) {
            'scheduled' => 'External Audit Scheduled',
            'started' => 'External Audit Started',
            'completed' => 'External Audit Completed',
            'cancelled' => 'External Audit Cancelled',
            default => 'External Audit Update',
        };

        $message = match ($this->action) {
            'scheduled' => "External audit {$this->audit->audit_number} scheduled for {$this->audit->scheduled_start_date->format('M d, Y')}",
            'started' => "External audit {$this->audit->audit_number} has started",
            'completed' => "External audit {$this->audit->audit_number} completed with result: " . ucfirst($this->audit->result),
            'cancelled' => "External audit {$this->audit->audit_number} has been cancelled",
            default => "External audit {$this->audit->audit_number} status changed",
        };

        $color = match ($this->action) {
            'scheduled' => 'info',
            'started' => 'primary',
            'completed' => $this->audit->result === 'passed' ? 'success' : ($this->audit->result === 'failed' ? 'danger' : 'warning'),
            'cancelled' => 'secondary',
            default => 'primary',
        };

        return [
            'type' => 'external_audit',
            'title' => $title,
            'message' => $message,
            'audit_id' => $this->audit->id,
            'audit_number' => $this->audit->audit_number,
            'action' => $this->action,
            'status' => $this->audit->status,
            'result' => $this->audit->result,
            'url' => route('external-audits.show', $this->audit),
            'icon' => 'tabler-clipboard-list',
            'color' => $color,
        ];
    }
}
