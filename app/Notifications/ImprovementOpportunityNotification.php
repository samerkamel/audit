<?php

namespace App\Notifications;

use App\Models\ImprovementOpportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImprovementOpportunityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ImprovementOpportunity $improvementOpportunity;
    protected string $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(ImprovementOpportunity $improvementOpportunity, string $action = 'issued')
    {
        $this->improvementOpportunity = $improvementOpportunity;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $io = $this->improvementOpportunity;
        $subject = $this->getSubject();
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("**IO Number:** {$io->io_number}")
            ->line("**Subject:** {$io->subject}")
            ->line("**Department:** {$io->toDepartment->name}")
            ->line("**Priority:** " . ucfirst($io->priority))
            ->action('View Improvement Opportunity', route('improvement-opportunities.show', $io))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'improvement_opportunity',
            'action' => $this->action,
            'io_id' => $this->improvementOpportunity->id,
            'io_number' => $this->improvementOpportunity->io_number,
            'subject' => $this->improvementOpportunity->subject,
            'priority' => $this->improvementOpportunity->priority,
            'status' => $this->improvementOpportunity->status,
            'department' => $this->improvementOpportunity->toDepartment->name ?? 'N/A',
            'message' => $this->getMessage(),
            'url' => route('improvement-opportunities.show', $this->improvementOpportunity),
        ];
    }

    /**
     * Get the notification subject based on action.
     */
    protected function getSubject(): string
    {
        $ioNumber = $this->improvementOpportunity->io_number;

        return match ($this->action) {
            'pending_approval' => "Improvement Opportunity {$ioNumber} - Awaiting Your Approval",
            'issued' => "Improvement Opportunity {$ioNumber} - Issued to Your Department",
            'rejected' => "Improvement Opportunity {$ioNumber} - Rejected for Editing",
            'response_submitted' => "Improvement Opportunity {$ioNumber} - Response Submitted",
            'response_accepted' => "Improvement Opportunity {$ioNumber} - Response Accepted",
            'response_rejected' => "Improvement Opportunity {$ioNumber} - Response Rejected",
            'closed' => "Improvement Opportunity {$ioNumber} - Closed",
            'reminder' => "Reminder: Improvement Opportunity {$ioNumber} - Response Required",
            default => "Improvement Opportunity {$ioNumber} - Update",
        };
    }

    /**
     * Get the notification message based on action.
     */
    protected function getMessage(): string
    {
        $ioNumber = $this->improvementOpportunity->io_number;

        return match ($this->action) {
            'pending_approval' => "Improvement Opportunity {$ioNumber} has been submitted and requires your approval.",
            'issued' => "Improvement Opportunity {$ioNumber} has been issued to your department. Please review and respond.",
            'rejected' => "Improvement Opportunity {$ioNumber} has been rejected and requires editing. Please review the feedback and make necessary changes.",
            'response_submitted' => "A response has been submitted for Improvement Opportunity {$ioNumber}.",
            'response_accepted' => "Your response for Improvement Opportunity {$ioNumber} has been accepted.",
            'response_rejected' => "Your response for Improvement Opportunity {$ioNumber} has been rejected. Please review the feedback and resubmit.",
            'closed' => "Improvement Opportunity {$ioNumber} has been successfully closed.",
            'reminder' => "This is a reminder that Improvement Opportunity {$ioNumber} requires your attention.",
            default => "There has been an update to Improvement Opportunity {$ioNumber}.",
        };
    }
}
