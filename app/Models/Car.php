<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'car_number',
        'source_type',
        'source_id',
        'audit_finding_id',
        'customer_complaint_id',
        'from_department_id',
        'to_department_id',
        'issued_date',
        'subject',
        'ncr_description',
        'clarification',
        'status',
        'priority',
        'issued_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the polymorphic source (audit finding, complaint, etc.).
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the audit finding if source is internal audit.
     */
    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditResponse::class, 'audit_finding_id');
    }

    /**
     * Get the customer complaint if source is complaint.
     */
    public function customerComplaint(): BelongsTo
    {
        return $this->belongsTo(CustomerComplaint::class);
    }

    /**
     * Get the department that issued the CAR (from).
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the department that should respond to the CAR (to).
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Get the user who issued the CAR.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who approved the CAR.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all responses for this CAR.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(CarResponse::class);
    }

    /**
     * Get the latest response for this CAR.
     */
    public function latestResponse()
    {
        return $this->hasOne(CarResponse::class)->latestOfMany();
    }

    /**
     * Get all follow-ups for this CAR.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(CarFollowUp::class);
    }

    /**
     * Scope a query to only include CARs with a specific status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter CARs by department.
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('to_department_id', $departmentId);
    }

    /**
     * Scope a query to filter CARs by source type.
     */
    public function scopeBySourceType($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope a query to only include overdue CARs.
     */
    public function scopeOverdue($query)
    {
        return $query->whereHas('latestResponse', function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('correction_target_date', '<', now())
                    ->whereNull('correction_actual_date');
            })->orWhere(function ($subQ) {
                $subQ->where('corrective_action_target_date', '<', now())
                    ->whereNull('corrective_action_actual_date');
            });
        });
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending_approval' => 'info',
            'issued' => 'primary',
            'in_progress' => 'warning',
            'pending_review' => 'info',
            'rejected_to_be_edited' => 'danger',
            'closed' => 'success',
            'late' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Generate next CAR number.
     */
    public static function generateCarNumber(): string
    {
        $year = date('y');
        $lastCar = static::where('car_number', 'like', "C{$year}%")
            ->orderBy('car_number', 'desc')
            ->first();

        if ($lastCar) {
            $lastNumber = (int) substr($lastCar->car_number, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('C%s%03d', $year, $nextNumber);
    }
}
