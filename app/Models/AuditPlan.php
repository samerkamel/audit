<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditPlan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'audit_type',
        'scope',
        'objectives',
        'lead_auditor_id',
        'created_by',
        'actual_start_date',
        'actual_end_date',
        'status',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the departments included in this audit plan.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'audit_plan_department')
            ->withPivot([
                'planned_start_date',
                'planned_end_date',
                'actual_start_date',
                'actual_end_date',
                'status',
                'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Get all checklist groups associated with this audit plan.
     */
    public function checklistGroups(): BelongsToMany
    {
        return $this->belongsToMany(CheckListGroup::class, 'audit_plan_checklist_groups')
            ->withPivot('department_id')
            ->withTimestamps();
    }

    /**
     * Get checklist groups for a specific department in this audit plan.
     */
    public function checklistGroupsForDepartment(int $departmentId)
    {
        return $this->checklistGroups()->wherePivot('department_id', $departmentId);
    }

    /**
     * Get the lead auditor of the audit plan.
     */
    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    /**
     * Get the user who created the audit plan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active audit plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->whereHas('departments', function ($q) use ($departmentId) {
            $q->where('departments.id', $departmentId);
        });
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'planned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the audit type label.
     */
    public function getAuditTypeLabelAttribute(): string
    {
        return match ($this->audit_type) {
            'internal' => 'Internal Audit',
            'external' => 'External Audit',
            'compliance' => 'Compliance Audit',
            'operational' => 'Operational Audit',
            'financial' => 'Financial Audit',
            'it' => 'IT Audit',
            'quality' => 'Quality Audit',
            default => ucfirst($this->audit_type),
        };
    }

}
