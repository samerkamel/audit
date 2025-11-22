<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Certificate $certificate
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
        $daysUntilExpiry = now()->diffInDays($this->certificate->expiry_date, false);
        $status = $daysUntilExpiry < 0 ? 'EXPIRED' : 'EXPIRING SOON';

        return (new MailMessage)
            ->subject("Certificate {$status}: {$this->certificate->certificate_number}")
            ->line("The certificate **{$this->certificate->certificate_number}** is {$status}.")
            ->line("**Certificate:** {$this->certificate->name}")
            ->line("**Standard:** {$this->certificate->standard}")
            ->line("**Expiry Date:** {$this->certificate->expiry_date->format('F d, Y')}")
            ->line("**Days " . ($daysUntilExpiry < 0 ? 'Expired' : 'Remaining') . ":** " . abs($daysUntilExpiry))
            ->action('View Certificate', route('certificates.show', $this->certificate))
            ->line('Please renew this certificate to maintain compliance.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysUntilExpiry = now()->diffInDays($this->certificate->expiry_date, false);
        $isExpired = $daysUntilExpiry < 0;

        return [
            'type' => 'certificate_expiry',
            'title' => $isExpired ? 'Certificate Expired' : 'Certificate Expiring Soon',
            'message' => "{$this->certificate->certificate_number} " . ($isExpired ? 'expired ' . abs($daysUntilExpiry) . ' days ago' : 'expires in ' . $daysUntilExpiry . ' days'),
            'icon' => 'certificate',
            'color' => $isExpired ? 'danger' : 'warning',
            'action_url' => route('certificates.show', $this->certificate),
            'action_text' => 'View Certificate',
            'notifiable_type' => Certificate::class,
            'notifiable_id' => $this->certificate->id,
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
