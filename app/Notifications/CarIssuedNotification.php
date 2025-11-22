<?php

namespace App\Notifications;

use App\Models\Car;
use App\Models\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CarIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Car $car;
    protected ?NotificationTemplate $template;

    /**
     * Create a new notification instance.
     */
    public function __construct(Car $car)
    {
        $this->car = $car;
        $this->template = NotificationTemplate::getByCode('car_issued');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($this->template) {
            return $this->template->getChannels();
        }

        return ['database', 'mail'];
    }

    /**
     * Get placeholder data for template rendering.
     */
    protected function getPlaceholderData(object $notifiable): array
    {
        return [
            'user_name' => $notifiable->name,
            'car_number' => $this->car->car_number,
            'car_subject' => $this->car->subject,
            'car_priority' => ucfirst($this->car->priority),
            'car_due_date' => $this->car->due_date?->format('Y-m-d') ?? 'Not set',
            'department_name' => $this->car->toDepartment?->name ?? 'N/A',
            'action_url' => route('cars.show', $this->car),
            'app_name' => config('app.name'),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->getPlaceholderData($notifiable);

        if ($this->template) {
            $subject = $this->template->renderEmailSubject($data);
            $body = $this->template->renderEmailBody($data);

            return (new MailMessage)
                ->subject($subject)
                ->greeting("Hello {$notifiable->name},")
                ->line($body)
                ->action('View CAR Details', $data['action_url']);
        }

        // Fallback if no template
        return (new MailMessage)
            ->subject("CAR Issued: {$this->car->car_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A Corrective Action Request (CAR) has been issued to your department.")
            ->line("CAR Number: {$this->car->car_number}")
            ->line("Subject: {$this->car->subject}")
            ->line("Priority: " . ucfirst($this->car->priority))
            ->line("Please review and submit your response as soon as possible.")
            ->action('View CAR Details', route('cars.show', $this->car))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $data = $this->getPlaceholderData($notifiable);

        if ($this->template) {
            return [
                'type' => 'car_issued',
                'title' => $this->template->renderNotificationTitle($data),
                'message' => $this->template->renderNotificationMessage($data),
                'car_id' => $this->car->id,
                'car_number' => $this->car->car_number,
                'priority' => $this->car->priority,
                'url' => $data['action_url'],
                'icon' => $this->template->notification_icon,
                'color' => $this->template->notification_color,
            ];
        }

        // Fallback if no template
        return [
            'type' => 'car_issued',
            'title' => 'CAR Issued to Your Department',
            'message' => "CAR {$this->car->car_number} has been issued: {$this->car->subject}",
            'car_id' => $this->car->id,
            'car_number' => $this->car->car_number,
            'priority' => $this->car->priority,
            'url' => route('cars.show', $this->car),
            'icon' => 'tabler-alert-circle',
            'color' => $this->getPriorityColor(),
        ];
    }

    /**
     * Get color based on priority.
     */
    protected function getPriorityColor(): string
    {
        return match ($this->car->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            default => 'primary',
        };
    }
}
