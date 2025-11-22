<?php

namespace App\Notifications;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarEscalationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Car $car;
    protected string $recipientType;
    protected int $daysSinceIssuance;

    /**
     * Create a new notification instance.
     *
     * @param Car $car
     * @param string $recipientType Type of recipient (sector_ceo, general_manager)
     * @param int $daysSinceIssuance Working days since CAR was issued
     */
    public function __construct(Car $car, string $recipientType = 'sector_ceo', int $daysSinceIssuance = 0)
    {
        $this->car = $car;
        $this->recipientType = $recipientType;
        $this->daysSinceIssuance = $daysSinceIssuance;
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
        $car = $this->car;
        $subject = $this->getSubject();

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Dear {$notifiable->name},")
            ->line("**ESCALATION NOTICE**")
            ->line('')
            ->line("This is an escalation notice for Corrective Action Request (CAR) {$car->car_number}.")
            ->line('')
            ->line("**Issue Summary:**")
            ->line("- CAR Number: {$car->car_number}")
            ->line("- Subject: {$car->subject}")
            ->line("- Responsible Department: {$car->toDepartment->name}")
            ->line("- Issued Date: {$car->issued_date->format('M d, Y')}")
            ->line("- Priority: " . ucfirst($car->priority))
            ->line("- Working Days Without Response: {$this->daysSinceIssuance}")
            ->line('')
            ->line("**Reason for Escalation:**")
            ->line("The responsible department has not submitted a response to this CAR within the expected timeframe ({$this->daysSinceIssuance} working days).")
            ->line('')
            ->line("**Recommended Action:**")
            ->line("Please follow up with the responsible department to ensure timely response and corrective action implementation.")
            ->action('View CAR Details', route('cars.show', $car))
            ->line('')
            ->line('This escalation was generated automatically by the Audit Management System.');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'car_escalation',
            'car_id' => $this->car->id,
            'car_number' => $this->car->car_number,
            'subject' => $this->car->subject,
            'priority' => $this->car->priority,
            'status' => $this->car->status,
            'department' => $this->car->toDepartment->name ?? 'N/A',
            'issued_date' => $this->car->issued_date->format('Y-m-d'),
            'days_since_issuance' => $this->daysSinceIssuance,
            'recipient_type' => $this->recipientType,
            'message' => $this->getMessage(),
            'url' => route('cars.show', $this->car),
            'requires_confirmation' => true,
        ];
    }

    /**
     * Get the notification subject.
     */
    protected function getSubject(): string
    {
        $carNumber = $this->car->car_number;
        $priority = strtoupper($this->car->priority);

        return "[ESCALATION] CAR {$carNumber} - No Response After {$this->daysSinceIssuance} Working Days [{$priority} Priority]";
    }

    /**
     * Get the notification message.
     */
    protected function getMessage(): string
    {
        return "ESCALATION: CAR {$this->car->car_number} has had no response for {$this->daysSinceIssuance} working days. Immediate attention required.";
    }
}
