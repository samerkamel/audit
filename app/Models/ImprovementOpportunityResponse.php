<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImprovementOpportunityResponse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'improvement_opportunity_id',
        'proposed_action',
        'implementation_plan',
        'target_date',
        'actual_date',
        'outcome',
        'attachments',
        'response_status',
        'rejection_reason',
        'responded_by',
        'responded_at',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_date' => 'date',
        'actual_date' => 'date',
        'attachments' => 'array',
        'responded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the Improvement Opportunity that this response belongs to.
     */
    public function improvementOpportunity(): BelongsTo
    {
        return $this->belongsTo(ImprovementOpportunity::class);
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
     * Check if the response is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->target_date < now() && $this->actual_date === null;
    }

    /**
     * Check if response is complete.
     */
    public function isComplete(): bool
    {
        return $this->actual_date !== null;
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
