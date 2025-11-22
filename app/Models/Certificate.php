<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'certificate_number',
        'standard',
        'certification_body',
        'certificate_type',
        'issue_date',
        'expiry_date',
        'status',
        'scope_of_certification',
        'covered_sites',
        'covered_processes',
        'certificate_file_path',
        'attachments',
        'notes',
        'issued_for_audit_id',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'covered_sites' => 'array',
        'covered_processes' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Relationships
     */
    public function issuedForAudit()
    {
        return $this->belongsTo(ExternalAudit::class, 'issued_for_audit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper Methods & Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'valid' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            'suspended' => 'warning',
            'revoked' => 'danger',
            default => 'secondary',
        };
    }

    public function getCertificateTypeLabelAttribute(): string
    {
        return match ($this->certificate_type) {
            'initial' => 'Initial Certification',
            'renewal' => 'Renewal',
            'transfer' => 'Transfer',
            default => ucfirst($this->certificate_type),
        };
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getValidityPeriodInYearsAttribute(): float
    {
        return round($this->issue_date->diffInYears($this->expiry_date, true), 1);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date < now()->toDateString();
    }

    public function isExpiringSoon(): bool
    {
        $daysUntilExpiry = $this->days_until_expiry;
        return $daysUntilExpiry > 0 && $daysUntilExpiry <= 90; // 90 days warning
    }

    public function isValid(): bool
    {
        return $this->status === 'valid' &&
               $this->issue_date <= now()->toDateString() &&
               $this->expiry_date >= now()->toDateString();
    }

    /**
     * Update certificate status based on expiry date
     */
    public function updateStatus(): void
    {
        if ($this->isExpired()) {
            $this->update(['status' => 'expired']);
        } elseif ($this->isExpiringSoon()) {
            $this->update(['status' => 'expiring_soon']);
        } elseif ($this->status === 'expiring_soon' && !$this->isExpiringSoon()) {
            $this->update(['status' => 'valid']);
        }
    }
}
