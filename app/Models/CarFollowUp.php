<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarFollowUp extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_id',
        'follow_up_type',
        'follow_up_status',
        'follow_up_notes',
        'followed_up_by',
        'followed_up_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'followed_up_at' => 'datetime',
    ];

    /**
     * Get the CAR that this follow-up belongs to.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user who performed the follow-up.
     */
    public function followedUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_up_by');
    }

    /**
     * Get the follow-up status badge color.
     */
    public function getFollowUpStatusColorAttribute(): string
    {
        return match ($this->follow_up_status) {
            'accepted' => 'success',
            'not_accepted' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get the follow-up type label.
     */
    public function getFollowUpTypeLabelAttribute(): string
    {
        return match ($this->follow_up_type) {
            'correction' => 'Short-term Action',
            'corrective_action' => 'Long-term Action',
            default => ucfirst(str_replace('_', ' ', $this->follow_up_type)),
        };
    }
}
