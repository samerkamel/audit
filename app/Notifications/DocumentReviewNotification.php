<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Document $document
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
        $daysOverdue = now()->diffInDays($this->document->next_review_date, false);
        $status = $daysOverdue < 0 ? 'OVERDUE FOR REVIEW' : 'REVIEW DUE SOON';

        return (new MailMessage)
            ->subject("Document {$status}: {$this->document->document_number}")
            ->line("The document **{$this->document->document_number}** is {$status}.")
            ->line("**Title:** {$this->document->title}")
            ->line("**Category:** {$this->document->category_label}")
            ->line("**Review Date:** {$this->document->next_review_date->format('F d, Y')}")
            ->line("**Days " . ($daysOverdue < 0 ? 'Overdue' : 'Remaining') . ":** " . abs($daysOverdue))
            ->action('View Document', route('documents.show', $this->document))
            ->line('Please review this document to ensure it remains current and accurate.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = now()->diffInDays($this->document->next_review_date, false);
        $isOverdue = $daysOverdue < 0;

        return [
            'type' => 'document_review',
            'title' => $isOverdue ? 'Document Review Overdue' : 'Document Review Due',
            'message' => "{$this->document->document_number} review is " . ($isOverdue ? abs($daysOverdue) . ' days overdue' : 'due in ' . $daysOverdue . ' days'),
            'icon' => 'file-text',
            'color' => $isOverdue ? 'danger' : 'warning',
            'action_url' => route('documents.show', $this->document),
            'action_text' => 'Review Document',
            'notifiable_type' => Document::class,
            'notifiable_id' => $this->document->id,
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
