<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditorProcedureRecommendation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auditor_procedure_recommendations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'procedure_id',
        'recommendation_level',
        'notes',
        'is_certified',
        'certification_date',
        'certification_expiry',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_certified' => 'boolean',
        'certification_date' => 'date',
        'certification_expiry' => 'date',
    ];

    /**
     * Get the auditor (user).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the procedure.
     */
    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    /**
     * Get the recommendation level badge color.
     */
    public function getRecommendationLevelColorAttribute(): string
    {
        return match ($this->recommendation_level) {
            'primary' => 'success',
            'secondary' => 'info',
            'backup' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Check if certification is expired.
     */
    public function isCertificationExpired(): bool
    {
        if (!$this->certification_expiry) {
            return false;
        }

        return $this->certification_expiry->isPast();
    }

    /**
     * Check if certification is expiring soon (within 30 days).
     */
    public function isCertificationExpiringSoon(): bool
    {
        if (!$this->certification_expiry) {
            return false;
        }

        return $this->certification_expiry->isBetween(now(), now()->addDays(30));
    }
}
