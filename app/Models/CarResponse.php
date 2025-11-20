<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarResponse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_id',
        'root_cause',
        'correction',
        'correction_target_date',
        'correction_actual_date',
        'corrective_action',
        'corrective_action_target_date',
        'corrective_action_actual_date',
        'attachments',
        'response_status',
        'responded_by',
        'responded_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'correction_target_date' => 'date',
        'correction_actual_date' => 'date',
        'corrective_action_target_date' => 'date',
        'corrective_action_actual_date' => 'date',
        'attachments' => 'array',
        'responded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the CAR that this response belongs to.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user who responded.
     */
    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Get the user who reviewed the response.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if correction is overdue.
     */
    public function isCorrectionOverdue(): bool
    {
        return $this->correction_target_date < now() && $this->correction_actual_date === null;
    }

    /**
     * Check if corrective action is overdue.
     */
    public function isCorrectiveActionOverdue(): bool
    {
        return $this->corrective_action_target_date < now() && $this->corrective_action_actual_date === null;
    }

    /**
     * Check if response is complete (both actions completed).
     */
    public function isComplete(): bool
    {
        return $this->correction_actual_date !== null && $this->corrective_action_actual_date !== null;
    }

    /**
     * Get the response status badge color.
     */
    public function getResponseStatusColorAttribute(): string
    {
        return match ($this->response_status) {
            'pending' => 'secondary',
            'submitted' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
