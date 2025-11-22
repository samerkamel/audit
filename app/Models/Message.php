<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'parent_id',
        'subject',
        'body',
        'related_type',
        'related_id',
        'priority',
        'read_at',
        'sender_deleted',
        'recipient_deleted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'sender_deleted' => 'boolean',
        'recipient_deleted' => 'boolean',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the parent message (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Get the replies to this message.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Get the related model (polymorphic: audit, car, complaint, etc.).
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for messages for a specific user (inbox).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('recipient_id', $userId)
            ->where('recipient_deleted', false);
    }

    /**
     * Scope for messages sent by a specific user (sent).
     */
    public function scopeSentBy($query, int $userId)
    {
        return $query->where('sender_id', $userId)
            ->where('sender_deleted', false);
    }

    /**
     * Scope for root messages (not replies).
     */
    public function scopeRootMessages($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'primary',
            'low' => 'secondary',
            default => 'primary',
        };
    }

    /**
     * Check if message is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get the related model name for display.
     */
    public function getRelatedTypeLabelAttribute(): ?string
    {
        if (!$this->related_type) {
            return null;
        }

        return match ($this->related_type) {
            AuditPlan::class => 'Audit Plan',
            Car::class => 'CAR',
            CustomerComplaint::class => 'Complaint',
            Document::class => 'Document',
            Certificate::class => 'Certificate',
            ExternalAudit::class => 'External Audit',
            ImprovementOpportunity::class => 'Improvement Opportunity',
            default => class_basename($this->related_type),
        };
    }

    /**
     * Get the thread (all messages in conversation).
     */
    public function getThread()
    {
        // Get root message
        $root = $this->parent_id ? $this->parent : $this;

        // Get all replies recursively
        return Message::where('id', $root->id)
            ->orWhere('parent_id', $root->id)
            ->with(['sender', 'recipient'])
            ->orderBy('created_at')
            ->get();
    }
}
