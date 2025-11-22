<?php

namespace App\Notifications;

use App\Models\CustomerComplaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CustomerComplaint $complaint
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
        $mail = (new MailMessage)
            ->subject("Complaint Assigned: {$this->complaint->complaint_number}")
            ->line("A new complaint has been assigned to you.")
            ->line("**Complaint:** {$this->complaint->complaint_number}")
            ->line("**Subject:** {$this->complaint->subject}")
            ->line("**Priority:** " . ucfirst($this->complaint->priority ?? 'normal'));

        if ($this->complaint->customer) {
            $mail->line("**Customer:** {$this->complaint->customer->name}");
        }

        if ($this->complaint->complaint_date) {
            $mail->line("**Received Date:** {$this->complaint->complaint_date->format('F d, Y')}");
        }

        return $mail
            ->action('View Complaint', route('complaints.show', $this->complaint))
            ->line('Please review and address this complaint promptly.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $priority = $this->complaint->priority ?? 'normal';

        return [
            'type' => 'complaint_assigned',
            'title' => 'Complaint Assigned',
            'message' => "{$this->complaint->complaint_number} - {$this->complaint->subject}",
            'icon' => 'message-report',
            'color' => $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info'),
            'action_url' => route('complaints.show', $this->complaint),
            'action_text' => 'View Complaint',
            'notifiable_type' => CustomerComplaint::class,
            'notifiable_id' => $this->complaint->id,
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
