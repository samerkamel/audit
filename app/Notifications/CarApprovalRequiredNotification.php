<?php

namespace App\Notifications;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Car $car;

    /**
     * Create a new notification instance.
     */
    public function __construct(Car $car)
    {
        $this->car = $car;
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
        $issuer = $this->car->issuedBy;
        $issuerName = $issuer ? $issuer->name : 'Unknown';

        return (new MailMessage)
            ->subject("CAR Requires Approval: {$this->car->car_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A Corrective Action Request (CAR) requires your approval.")
            ->line("CAR Number: {$this->car->car_number}")
            ->line("Subject: {$this->car->subject}")
            ->line("Priority: " . ucfirst($this->car->priority))
            ->line("Submitted by: {$issuerName}")
            ->action('Review CAR', route('cars.show', $this->car))
            ->line('Please review and approve or reject this CAR.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'car_approval_required',
            'title' => 'CAR Approval Required',
            'message' => "CAR {$this->car->car_number} requires your approval: {$this->car->subject}",
            'car_id' => $this->car->id,
            'car_number' => $this->car->car_number,
            'priority' => $this->car->priority,
            'url' => route('cars.show', $this->car),
            'icon' => 'tabler-clipboard-check',
            'color' => 'warning',
        ];
    }
}
