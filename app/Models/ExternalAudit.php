<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalAudit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'audit_number',
        'audit_type',
        'certification_body',
        'standard',
        'lead_auditor_name',
        'lead_auditor_email',
        'lead_auditor_phone',
        'scheduled_start_date',
        'scheduled_end_date',
        'actual_start_date',
        'actual_end_date',
        'audited_departments',
        'audited_processes',
        'scope_description',
        'result',
        'major_ncrs_count',
        'minor_ncrs_count',
        'observations_count',
        'opportunities_count',
        'audit_summary',
        'strengths',
        'areas_for_improvement',
        'audit_report_path',
        'attachments',
        'next_audit_date',
        'status',
        'created_by',
        'coordinator_id',
    ];

    protected $casts = [
        'scheduled_start_date' => 'date',
        'scheduled_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'next_audit_date' => 'date',
        'audited_departments' => 'array',
        'audited_processes' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Generate unique audit number
     */
    public static function generateAuditNumber(): string
    {
        $year = date('y');
        $prefix = "EXT-{$year}-";

        // Include soft-deleted records to avoid duplicate key errors
        $lastAudit = static::withTrashed()
            ->where('audit_number', 'like', "{$prefix}%")
            ->orderBy('audit_number', 'desc')
            ->first();

        if ($lastAudit) {
            $lastNumber = (int) substr($lastAudit->audit_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'issued_for_audit_id');
    }

    /**
     * Helper Methods & Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getResultColorAttribute(): string
    {
        return match ($this->result) {
            'passed' => 'success',
            'conditional' => 'warning',
            'failed' => 'danger',
            'pending' => 'info',
            default => 'secondary',
        };
    }

    public function getAuditTypeLabelAttribute(): string
    {
        return match ($this->audit_type) {
            'initial_certification' => 'Initial Certification',
            'surveillance' => 'Surveillance Audit',
            'recertification' => 'Recertification Audit',
            'special' => 'Special Audit',
            'follow_up' => 'Follow-up Audit',
            default => ucfirst(str_replace('_', ' ', $this->audit_type)),
        };
    }

    public function getTotalFindingsAttribute(): int
    {
        return $this->major_ncrs_count + $this->minor_ncrs_count + $this->observations_count;
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || $this->status === 'cancelled') {
            return false;
        }

        return $this->scheduled_end_date < now()->toDateString();
    }

    public function isUpcoming(): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        return $this->scheduled_start_date > now()->toDateString() &&
               $this->scheduled_start_date <= now()->addDays(30)->toDateString();
    }

    public function canStart(): bool
    {
        return $this->status === 'scheduled';
    }

    public function canComplete(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canGenerateCertificate(): bool
    {
        return $this->status === 'completed' &&
               ($this->result === 'passed' || $this->result === 'conditional') &&
               !$this->certificate()->exists();
    }

    public function getDurationInDaysAttribute(): int
    {
        if ($this->actual_start_date && $this->actual_end_date) {
            return $this->actual_start_date->diffInDays($this->actual_end_date) + 1;
        }

        return $this->scheduled_start_date->diffInDays($this->scheduled_end_date) + 1;
    }
}
