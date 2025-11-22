<?php

namespace App\Notifications;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarClosedNotification extends Notification implements ShouldQueue
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
            ->subject("CAR Closed: {$this->car->car_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A Corrective Action Request (CAR) has been successfully closed.")
            ->line("CAR Number: {$this->car->car_number}")
            ->line("Subject: {$this->car->subject}")
            ->line("Closed on: " . now()->format('M d, Y'))
            ->action('View CAR Details', route('cars.show', $this->car))
            ->line('Thank you for addressing this corrective action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'car_closed',
            'title' => 'CAR Successfully Closed',
            'message' => "CAR {$this->car->car_number} has been closed: {$this->car->subject}",
            'car_id' => $this->car->id,
            'car_number' => $this->car->car_number,
            'url' => route('cars.show', $this->car),
            'icon' => 'tabler-check',
            'color' => 'success',
        ];
    }
}
