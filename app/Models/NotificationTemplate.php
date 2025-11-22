<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'email_subject',
        'email_body',
        'notification_title',
        'notification_message',
        'notification_icon',
        'notification_color',
        'send_email',
        'send_database',
        'is_active',
        'available_placeholders',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_database' => 'boolean',
        'is_active' => 'boolean',
        'available_placeholders' => 'array',
    ];

    /**
     * Get template by code.
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Render a template string with placeholders replaced.
     *
     * @param string $template The template string with {placeholder} syntax
     * @param array $data Key-value pairs of placeholder => value
     * @return string
     */
    public static function render(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            // Support both {placeholder} and {{placeholder}} syntax
            $template = str_replace('{' . $key . '}', $value ?? '', $template);
            $template = str_replace('{{' . $key . '}}', $value ?? '', $template);
        }

        return $template;
    }

    /**
     * Get rendered email subject.
     */
    public function renderEmailSubject(array $data): string
    {
        return self::render($this->email_subject, $data);
    }

    /**
     * Get rendered email body.
     */
    public function renderEmailBody(array $data): string
    {
        return self::render($this->email_body, $data);
    }

    /**
     * Get rendered notification title.
     */
    public function renderNotificationTitle(array $data): string
    {
        return self::render($this->notification_title, $data);
    }

    /**
     * Get rendered notification message.
     */
    public function renderNotificationMessage(array $data): string
    {
        return self::render($this->notification_message, $data);
    }

    /**
     * Get channels array based on configuration.
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
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'car' => 'Corrective Action Requests',
            'audit' => 'Audits',
            'document' => 'Document Control',
            'certificate' => 'Certificates',
            'complaint' => 'Customer Complaints',
            'external_audit' => 'External Audits',
            default => Str::title(str_replace('_', ' ', $this->category)),
        };
    }

    /**
     * Get color class for display.
     */
    public function getColorClassAttribute(): string
    {
        return match ($this->notification_color) {
            'danger' => 'bg-label-danger',
            'warning' => 'bg-label-warning',
            'success' => 'bg-label-success',
            'info' => 'bg-label-info',
            default => 'bg-label-primary',
        };
    }

    /**
     * Scope to filter by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get only active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            'car' => 'Corrective Action Requests',
            'audit' => 'Audits',
            'document' => 'Document Control',
            'certificate' => 'Certificates',
            'complaint' => 'Customer Complaints',
            'external_audit' => 'External Audits',
        ];
    }

    /**
     * Get available colors.
     */
    public static function getColors(): array
    {
        return [
            'primary' => 'Primary (Blue)',
            'success' => 'Success (Green)',
            'warning' => 'Warning (Yellow)',
            'danger' => 'Danger (Red)',
            'info' => 'Info (Cyan)',
        ];
    }

    /**
     * Get available icons.
     */
    public static function getIcons(): array
    {
        return [
            'tabler-bell' => 'Bell',
            'tabler-alert-circle' => 'Alert Circle',
            'tabler-alert-triangle' => 'Alert Triangle',
            'tabler-check' => 'Check',
            'tabler-x' => 'X',
            'tabler-clipboard-check' => 'Clipboard Check',
            'tabler-file-text' => 'File Text',
            'tabler-file-certificate' => 'File Certificate',
            'tabler-award' => 'Award',
            'tabler-message-report' => 'Message Report',
            'tabler-calendar' => 'Calendar',
            'tabler-clock' => 'Clock',
            'tabler-user' => 'User',
            'tabler-mail' => 'Mail',
        ];
    }
}
