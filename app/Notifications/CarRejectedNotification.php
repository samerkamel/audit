<?php

namespace App\Notifications;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarRejectedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject("CAR Rejected: {$this->car->car_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your Corrective Action Request (CAR) has been rejected and requires editing.")
            ->line("CAR Number: {$this->car->car_number}")
            ->line("Subject: {$this->car->subject}")
            ->line("Rejection Reason: {$this->car->clarification}")
            ->action('Edit CAR', route('cars.edit', $this->car))
            ->line('Please address the feedback and resubmit for approval.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'car_rejected',
            'title' => 'CAR Rejected for Editing',
            'message' => "CAR {$this->car->car_number} was rejected: {$this->car->clarification}",
            'car_id' => $this->car->id,
            'car_number' => $this->car->car_number,
            'rejection_reason' => $this->car->clarification,
            'url' => route('cars.edit', $this->car),
            'icon' => 'tabler-x',
            'color' => 'danger',
        ];
    }
}
