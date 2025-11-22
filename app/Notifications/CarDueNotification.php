<?php

namespace App\Notifications;

use App\Models\Car;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Car $car
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
        $daysOverdue = now()->diffInDays($this->car->due_date, false);
        $status = $daysOverdue < 0 ? 'OVERDUE' : 'DUE SOON';

        return (new MailMessage)
            ->subject("CAR {$status}: {$this->car->car_number}")
            ->line("The CAR **{$this->car->car_number}** is {$status}.")
            ->line("**Finding:** {$this->car->description}")
            ->line("**Due Date:** {$this->car->due_date->format('F d, Y')}")
            ->line("**Days " . ($daysOverdue < 0 ? 'Overdue' : 'Remaining') . ":** " . abs($daysOverdue))
            ->action('View CAR', route('cars.show', $this->car))
            ->line('Please take immediate action to address this CAR.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $daysOverdue = now()->diffInDays($this->car->due_date, false);
        $isOverdue = $daysOverdue < 0;

        return [
            'type' => 'car_due',
            'title' => $isOverdue ? 'CAR Overdue' : 'CAR Due Soon',
            'message' => "{$this->car->car_number} is " . ($isOverdue ? abs($daysOverdue) . ' days overdue' : 'due in ' . $daysOverdue . ' days'),
            'icon' => 'alert-triangle',
            'color' => $isOverdue ? 'danger' : 'warning',
            'action_url' => route('cars.show', $this->car),
            'action_text' => 'View CAR',
            'notifiable_type' => Car::class,
            'notifiable_id' => $this->car->id,
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
