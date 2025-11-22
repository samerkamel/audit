<?php

namespace App\Notifications;

use App\Models\Car;
use App\Models\CarResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?string $dueType;
    protected ?\Carbon\Carbon $targetDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Car $car,
        ?string $dueType = null
    ) {
        $this->dueType = $dueType;
        $this->determineTargetDate();
    }

    /**
     * Determine the target date based on CAR response.
     */
    protected function determineTargetDate(): void
    {
        $response = $this->car->latestResponse;

        if (!$response) {
            $this->targetDate = null;
            return;
        }

        // Check corrective action first (long-term), then correction (short-term)
        if ($this->dueType === 'correction' && $response->correction_target_date) {
            $this->targetDate = $response->correction_target_date;
        } elseif ($this->dueType === 'corrective_action' && $response->corrective_action_target_date) {
            $this->targetDate = $response->corrective_action_target_date;
        } elseif ($response->corrective_action_target_date && !$response->corrective_action_actual_date) {
            $this->targetDate = $response->corrective_action_target_date;
            $this->dueType = 'corrective_action';
        } elseif ($response->correction_target_date && !$response->correction_actual_date) {
            $this->targetDate = $response->correction_target_date;
            $this->dueType = 'correction';
        } else {
            $this->targetDate = null;
        }
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
        if (!$this->targetDate) {
            return (new MailMessage)
                ->subject("CAR Action Required: {$this->car->car_number}")
                ->line("The CAR **{$this->car->car_number}** requires attention.")
                ->line("**Subject:** {$this->car->subject}")
                ->action('View CAR', route('cars.show', $this->car))
                ->line('Please take action on this CAR.');
        }

        $daysRemaining = now()->diffInDays($this->targetDate, false);
        $status = $daysRemaining < 0 ? 'OVERDUE' : 'DUE SOON';
        $actionType = $this->dueType === 'correction' ? 'Correction' : 'Corrective Action';

        return (new MailMessage)
            ->subject("CAR {$actionType} {$status}: {$this->car->car_number}")
            ->line("The {$actionType} for CAR **{$this->car->car_number}** is {$status}.")
            ->line("**Subject:** {$this->car->subject}")
            ->line("**Target Date:** {$this->targetDate->format('F d, Y')}")
            ->line("**Days " . ($daysRemaining < 0 ? 'Overdue' : 'Remaining') . ":** " . abs($daysRemaining))
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
        if (!$this->targetDate) {
            return [
                'type' => 'car_due',
                'title' => 'CAR Requires Action',
                'message' => "{$this->car->car_number} requires attention",
                'icon' => 'alert-triangle',
                'color' => 'warning',
                'action_url' => route('cars.show', $this->car),
                'action_text' => 'View CAR',
                'notifiable_type' => Car::class,
                'notifiable_id' => $this->car->id,
            ];
        }

        $daysRemaining = now()->diffInDays($this->targetDate, false);
        $isOverdue = $daysRemaining < 0;
        $actionType = $this->dueType === 'correction' ? 'Correction' : 'Corrective Action';

        return [
            'type' => 'car_due',
            'title' => $isOverdue ? "CAR {$actionType} Overdue" : "CAR {$actionType} Due Soon",
            'message' => "{$this->car->car_number}: {$actionType} " . ($isOverdue ? abs($daysRemaining) . ' days overdue' : 'due in ' . $daysRemaining . ' days'),
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
