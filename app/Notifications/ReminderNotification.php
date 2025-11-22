<?php

namespace App\Notifications;

use App\Models\ReminderSetting;
use App\Models\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ReminderSetting $setting;
    protected $entity;
    protected int $intervalHours;
    protected ?NotificationTemplate $template;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReminderSetting $setting, $entity, int $intervalHours)
    {
        $this->setting = $setting;
        $this->entity = $entity;
        $this->intervalHours = $intervalHours;
        $this->template = $setting->notification_template_code
            ? NotificationTemplate::getByCode($setting->notification_template_code)
            : null;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return $this->setting->getChannels();
    }

    /**
     * Get placeholder data for template rendering.
     */
    protected function getPlaceholderData(object $notifiable): array
    {
        $intervalLabel = ReminderSetting::getIntervalOptions()[$this->intervalHours] ?? "{$this->intervalHours} hours";

        $data = [
            'user_name' => $notifiable->name,
            'interval' => $intervalLabel,
            'event_type' => $this->setting->event_type_label,
            'entity_type' => $this->setting->entity_type_label,
            'app_name' => config('app.name'),
        ];

        // Add entity-specific placeholders
        return array_merge($data, $this->getEntityData());
    }

    /**
     * Get entity-specific placeholder data.
     */
    protected function getEntityData(): array
    {
        return match ($this->setting->entity_type) {
            'audit_plan' => [
                'audit_number' => $this->entity->audit_number ?? 'N/A',
                'audit_date' => $this->entity->start_date?->format('Y-m-d H:i') ?? 'N/A',
                'audit_type' => 'Internal Audit',
                'department_name' => $this->entity->department?->name ?? 'N/A',
                'action_url' => route('audit-plans.show', $this->entity),
            ],
            'external_audit' => [
                'audit_number' => $this->entity->audit_number ?? 'N/A',
                'audit_date' => $this->entity->scheduled_start_date?->format('Y-m-d H:i') ?? 'N/A',
                'audit_type' => $this->entity->audit_type ?? 'External Audit',
                'certification_body' => $this->entity->certification_body ?? 'N/A',
                'standard' => $this->entity->standard ?? 'N/A',
                'action_url' => route('external-audits.show', $this->entity),
            ],
            'car' => [
                'car_number' => $this->entity->car_number ?? 'N/A',
                'car_subject' => $this->entity->subject ?? 'N/A',
                'car_priority' => ucfirst($this->entity->priority ?? 'N/A'),
                'car_due_date' => $this->entity->due_date?->format('Y-m-d') ?? 'N/A',
                'department_name' => $this->entity->toDepartment?->name ?? 'N/A',
                'action_url' => route('cars.show', $this->entity),
            ],
            'certificate' => [
                'certificate_number' => $this->entity->certificate_number ?? 'N/A',
                'certificate_standard' => $this->entity->standard ?? 'N/A',
                'certificate_expiry' => $this->entity->expiry_date?->format('Y-m-d') ?? 'N/A',
                'certification_body' => $this->entity->certification_body ?? 'N/A',
                'action_url' => route('certificates.show', $this->entity),
            ],
            'document' => [
                'document_number' => $this->entity->document_number ?? 'N/A',
                'document_title' => $this->entity->title ?? 'N/A',
                'document_status' => $this->entity->status ?? 'N/A',
                'action_url' => route('documents.show', $this->entity),
            ],
            default => [],
        };
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->getPlaceholderData($notifiable);
        $intervalLabel = ReminderSetting::getIntervalOptions()[$this->intervalHours] ?? "{$this->intervalHours} hours";

        if ($this->template) {
            return (new MailMessage)
                ->subject($this->template->renderEmailSubject($data))
                ->greeting("Hello {$notifiable->name},")
                ->line($this->template->renderEmailBody($data))
                ->action('View Details', $data['action_url'] ?? config('app.url'));
        }

        // Fallback generic email
        $subject = "Reminder: {$this->setting->name} in {$intervalLabel}";
        $message = $this->getGenericMessage($data, $intervalLabel);

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->action('View Details', $data['action_url'] ?? config('app.url'))
            ->line('Thank you for using our application.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $data = $this->getPlaceholderData($notifiable);
        $intervalLabel = ReminderSetting::getIntervalOptions()[$this->intervalHours] ?? "{$this->intervalHours} hours";

        if ($this->template) {
            return [
                'type' => 'reminder',
                'reminder_type' => $this->setting->entity_type,
                'title' => $this->template->renderNotificationTitle($data),
                'message' => $this->template->renderNotificationMessage($data),
                'entity_type' => $this->setting->entity_type,
                'entity_id' => $this->entity->id,
                'url' => $data['action_url'] ?? null,
                'icon' => $this->template->notification_icon ?? 'tabler-clock',
                'color' => $this->template->notification_color ?? 'warning',
            ];
        }

        // Fallback generic notification
        return [
            'type' => 'reminder',
            'reminder_type' => $this->setting->entity_type,
            'title' => "Upcoming: {$this->setting->name}",
            'message' => $this->getGenericMessage($data, $intervalLabel),
            'entity_type' => $this->setting->entity_type,
            'entity_id' => $this->entity->id,
            'url' => $data['action_url'] ?? null,
            'icon' => 'tabler-clock',
            'color' => 'warning',
        ];
    }

    /**
     * Generate a generic message based on entity type.
     */
    protected function getGenericMessage(array $data, string $intervalLabel): string
    {
        return match ($this->setting->entity_type) {
            'audit_plan' => "Audit {$data['audit_number']} is scheduled to start in {$intervalLabel}.",
            'external_audit' => "External audit {$data['audit_number']} by {$data['certification_body']} is scheduled in {$intervalLabel}.",
            'car' => "CAR {$data['car_number']} is due in {$intervalLabel}. Please ensure your response is submitted.",
            'certificate' => "Certificate {$data['certificate_number']} ({$data['certificate_standard']}) expires in {$intervalLabel}.",
            'document' => "Document {$data['document_number']} is due for review in {$intervalLabel}.",
            default => "You have an upcoming event in {$intervalLabel}.",
        };
    }
}
