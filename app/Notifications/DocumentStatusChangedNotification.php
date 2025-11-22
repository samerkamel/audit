<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected string $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, string $action)
    {
        $this->document = $document;
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
            case 'submitted_for_review':
                $message
                    ->subject("Document Pending Review: {$this->document->document_number}")
                    ->line("A document has been submitted for your review.")
                    ->line("Document Number: {$this->document->document_number}")
                    ->line("Title: {$this->document->title}")
                    ->line("Category: " . ucfirst(str_replace('_', ' ', $this->document->category)));
                break;

            case 'reviewed':
                $message
                    ->subject("Document Reviewed: {$this->document->document_number}")
                    ->line("A document has been reviewed and is pending approval.")
                    ->line("Document Number: {$this->document->document_number}")
                    ->line("Title: {$this->document->title}");
                break;

            case 'approved':
                $message
                    ->subject("Document Approved: {$this->document->document_number}")
                    ->line("Your document has been approved.")
                    ->line("Document Number: {$this->document->document_number}")
                    ->line("Title: {$this->document->title}");
                break;

            case 'effective':
                $message
                    ->subject("Document Now Effective: {$this->document->document_number}")
                    ->line("A document is now effective and available for use.")
                    ->line("Document Number: {$this->document->document_number}")
                    ->line("Title: {$this->document->title}")
                    ->line("Version: {$this->document->version}.{$this->document->revision_number}");
                break;

            case 'obsolete':
                $message
                    ->subject("Document Made Obsolete: {$this->document->document_number}")
                    ->line("A document has been marked as obsolete.")
                    ->line("Document Number: {$this->document->document_number}")
                    ->line("Title: {$this->document->title}");
                break;

            default:
                $message
                    ->subject("Document Update: {$this->document->document_number}")
                    ->line("A document status has changed.")
                    ->line("Document Number: {$this->document->document_number}");
        }

        return $message
            ->action('View Document', route('documents.show', $this->document))
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
            'submitted_for_review' => 'Document Pending Review',
            'reviewed' => 'Document Reviewed',
            'approved' => 'Document Approved',
            'effective' => 'Document Now Effective',
            'obsolete' => 'Document Made Obsolete',
            default => 'Document Status Updated',
        };

        $message = match ($this->action) {
            'submitted_for_review' => "Document {$this->document->document_number} is pending your review",
            'reviewed' => "Document {$this->document->document_number} has been reviewed and pending approval",
            'approved' => "Document {$this->document->document_number} has been approved",
            'effective' => "Document {$this->document->document_number} is now effective",
            'obsolete' => "Document {$this->document->document_number} has been made obsolete",
            default => "Document {$this->document->document_number} status changed to {$this->document->status}",
        };

        $color = match ($this->action) {
            'submitted_for_review' => 'warning',
            'reviewed' => 'info',
            'approved' => 'success',
            'effective' => 'success',
            'obsolete' => 'secondary',
            default => 'primary',
        };

        return [
            'type' => 'document_status_changed',
            'title' => $title,
            'message' => $message,
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'action' => $this->action,
            'status' => $this->document->status,
            'url' => route('documents.show', $this->document),
            'icon' => 'tabler-file-text',
            'color' => $color,
        ];
    }
}
