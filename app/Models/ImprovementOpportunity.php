<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImprovementOpportunity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'io_number',
        'source_type',
        'source_id',
        'audit_finding_id',
        'from_department_id',
        'to_department_id',
        'issued_date',
        'subject',
        'observation_description',
        'improvement_suggestion',
        'clarification',
        'status',
        'priority',
        'issued_by',
        'approved_by',
        'approved_at',
        'closed_by',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_date' => 'date',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the polymorphic source (audit finding, etc.).
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
     * Get the department that issued the IO (from).
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the department that should respond to the IO (to).
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Get the user who issued the IO.
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who approved the IO.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who closed the IO.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all responses for this IO.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ImprovementOpportunityResponse::class);
    }

    /**
     * Get the latest response for this IO.
     */
    public function latestResponse()
    {
        return $this->hasOne(ImprovementOpportunityResponse::class)->latestOfMany();
    }

    /**
     * Scope a query to only include IOs with a specific status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter IOs by department.
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('to_department_id', $departmentId);
    }

    /**
     * Scope a query to filter IOs by source type.
     */
    public function scopeBySourceType($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope a query to only include overdue IOs.
     */
    public function scopeOverdue($query)
    {
        return $query->whereHas('latestResponse', function ($q) {
            $q->where('target_date', '<', now())
                ->whereNull('actual_date');
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
            default => 'secondary',
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Generate next IO number with duplicate handling.
     * Format: IO25001 (IO + 2-digit year + 3-digit sequence)
     */
    public static function generateIoNumber(): string
    {
        $year = date('y');
        $maxAttempts = 100; // Safety limit to prevent infinite loops

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Find the highest IO number for this year (including soft-deleted)
            $lastIo = static::withTrashed()
                ->where('io_number', 'like', "IO{$year}%")
                ->orderBy('io_number', 'desc')
                ->first();

            if ($lastIo) {
                $lastNumber = (int) substr($lastIo->io_number, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $ioNumber = sprintf('IO%s%03d', $year, $nextNumber);

            // Check if this number already exists (including soft-deleted)
            if (!static::withTrashed()->where('io_number', $ioNumber)->exists()) {
                return $ioNumber;
            }

            // If it exists, the loop will continue and find the next highest number
        }

        // Fallback: use timestamp-based unique number if all attempts failed
        return sprintf('IO%s%s', $year, substr(time(), -5));
    }
}
