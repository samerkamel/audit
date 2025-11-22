<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Certificate $certificate;
    protected string $action;
    protected string $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Certificate $certificate, string $action, string $previousStatus = '')
    {
        $this->certificate = $certificate;
        $this->action = $action;
        $this->previousStatus = $previousStatus;
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
            case 'created':
                $message
                    ->subject("New Certificate Created: {$this->certificate->certificate_number}")
                    ->line("A new certificate has been created for {$this->certificate->standard}.")
                    ->line("Certificate Number: {$this->certificate->certificate_number}")
                    ->line("Certification Body: {$this->certificate->certification_body}")
                    ->line("Expiry Date: " . $this->certificate->expiry_date->format('M d, Y'));
                break;

            case 'suspended':
                $message
                    ->subject("Certificate Suspended: {$this->certificate->certificate_number}")
                    ->line("A certificate has been suspended and requires attention.")
                    ->line("Certificate Number: {$this->certificate->certificate_number}")
                    ->line("Standard: {$this->certificate->standard}")
                    ->error();
                break;

            case 'revoked':
                $message
                    ->subject("Certificate Revoked: {$this->certificate->certificate_number}")
                    ->line("A certificate has been revoked.")
                    ->line("Certificate Number: {$this->certificate->certificate_number}")
                    ->line("Standard: {$this->certificate->standard}")
                    ->line("This is a critical notification that requires immediate attention.")
                    ->error();
                break;

            case 'reinstated':
                $message
                    ->subject("Certificate Reinstated: {$this->certificate->certificate_number}")
                    ->line("A previously suspended certificate has been reinstated.")
                    ->line("Certificate Number: {$this->certificate->certificate_number}")
                    ->line("Standard: {$this->certificate->standard}")
                    ->line("Current Status: " . ucfirst($this->certificate->status));
                break;

            default:
                $message
                    ->subject("Certificate Update: {$this->certificate->certificate_number}")
                    ->line("A certificate status has been updated.")
                    ->line("Certificate Number: {$this->certificate->certificate_number}");
        }

        return $message
            ->action('View Certificate', route('certificates.show', $this->certificate))
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
            'created' => 'New Certificate Created',
            'suspended' => 'Certificate Suspended',
            'revoked' => 'Certificate Revoked',
            'reinstated' => 'Certificate Reinstated',
            default => 'Certificate Status Updated',
        };

        $message = match ($this->action) {
            'created' => "Certificate {$this->certificate->certificate_number} created for {$this->certificate->standard}",
            'suspended' => "Certificate {$this->certificate->certificate_number} has been suspended",
            'revoked' => "Certificate {$this->certificate->certificate_number} has been revoked",
            'reinstated' => "Certificate {$this->certificate->certificate_number} has been reinstated",
            default => "Certificate {$this->certificate->certificate_number} status changed to {$this->certificate->status}",
        };

        $color = match ($this->action) {
            'created' => 'success',
            'suspended' => 'warning',
            'revoked' => 'danger',
            'reinstated' => 'info',
            default => 'primary',
        };

        return [
            'type' => 'certificate_status_changed',
            'title' => $title,
            'message' => $message,
            'certificate_id' => $this->certificate->id,
            'certificate_number' => $this->certificate->certificate_number,
            'action' => $this->action,
            'status' => $this->certificate->status,
            'url' => route('certificates.show', $this->certificate),
            'icon' => 'tabler-certificate',
            'color' => $color,
        ];
    }
}
