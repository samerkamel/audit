<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'event_type',
        'name',
        'description',
        'intervals',
        'is_active',
        'send_email',
        'send_database',
        'notification_template_code',
    ];

    protected $casts = [
        'intervals' => 'array',
        'is_active' => 'boolean',
        'send_email' => 'boolean',
        'send_database' => 'boolean',
    ];

    /**
     * Get entity types with labels.
     */
    public static function getEntityTypes(): array
    {
        return [
            'audit_plan' => 'Internal Audit Plans',
            'external_audit' => 'External Audits',
            'car' => 'Corrective Action Requests',
            'document' => 'Documents',
            'certificate' => 'Certificates',
        ];
    }

    /**
     * Get event types with labels.
     */
    public static function getEventTypes(): array
    {
        return [
            'start_date' => 'Start Date',
            'due_date' => 'Due Date',
            'expiry_date' => 'Expiry Date',
            'review_date' => 'Review Date',
            'end_date' => 'End Date',
        ];
    }

    /**
     * Get predefined interval options.
     */
    public static function getIntervalOptions(): array
    {
        return [
            1 => '1 hour',
            2 => '2 hours',
            4 => '4 hours',
            8 => '8 hours',
            12 => '12 hours',
            24 => '1 day',
            48 => '2 days',
            72 => '3 days',
            120 => '5 days',
            168 => '1 week',
            336 => '2 weeks',
            720 => '1 month',
        ];
    }

    /**
     * Get entity type label.
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return self::getEntityTypes()[$this->entity_type] ?? $this->entity_type;
    }

    /**
     * Get event type label.
     */
    public function getEventTypeLabelAttribute(): string
    {
        return self::getEventTypes()[$this->event_type] ?? $this->event_type;
    }

    /**
     * Get formatted intervals for display.
     */
    public function getFormattedIntervalsAttribute(): array
    {
        $options = self::getIntervalOptions();
        $formatted = [];

        foreach ($this->intervals ?? [] as $hours) {
            $formatted[] = $options[$hours] ?? "{$hours} hours";
        }

        return $formatted;
    }

    /**
     * Get notification channels array.
     */
    public function getChannels(): array
    {
        $channels = [];

        if ($this->send_database) {
            $channels[] = 'database';
        }

        if ($this->send_email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get associated notification template.
     */
    public function notificationTemplate()
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_code', 'code');
    }

    /**
     * Scope to get active reminders.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by entity type.
     */
    public function scopeForEntity($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Check if a reminder was already sent.
     */
    public static function wasReminderSent(string $entityType, int $entityId, string $eventType, int $intervalHours, int $userId): bool
    {
        return SentReminder::where([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'event_type' => $eventType,
            'interval_hours' => $intervalHours,
            'user_id' => $userId,
        ])->exists();
    }

    /**
     * Mark a reminder as sent.
     */
    public static function markReminderSent(string $entityType, int $entityId, string $eventType, int $intervalHours, int $userId): SentReminder
    {
        return SentReminder::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'event_type' => $eventType,
            'interval_hours' => $intervalHours,
            'user_id' => $userId,
            'sent_at' => now(),
        ]);
    }
}
