<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditResponse extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audit_plan_id',
        'department_id',
        'checklist_group_id',
        'audit_question_id',
        'auditor_id',
        'response',
        'comments',
        'evidence_file',
        'audited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'audited_at' => 'datetime',
    ];

    /**
     * Get the audit plan this response belongs to.
     */
    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }

    /**
     * Get the department this response is for.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the checklist group this response belongs to.
     */
    public function checklistGroup(): BelongsTo
    {
        return $this->belongsTo(CheckListGroup::class);
    }

    /**
     * Get the audit question this response is for.
     */
    public function auditQuestion(): BelongsTo
    {
        return $this->belongsTo(AuditQuestion::class);
    }

    /**
     * Get the auditor who submitted this response.
     */
    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    /**
     * Scope a query to only include responses for a specific audit plan.
     */
    public function scopeForAuditPlan($query, int $auditPlanId)
    {
        return $query->where('audit_plan_id', $auditPlanId);
    }

    /**
     * Scope a query to only include responses for a specific department.
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to only include responses by a specific auditor.
     */
    public function scopeByAuditor($query, int $auditorId)
    {
        return $query->where('auditor_id', $auditorId);
    }

    /**
     * Get the response status badge color.
     */
    public function getResponseColorAttribute(): string
    {
        return match ($this->response) {
            'complied' => 'success',
            'not_complied' => 'danger',
            'not_applicable' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get the response label.
     */
    public function getResponseLabelAttribute(): string
    {
        return match ($this->response) {
            'complied' => 'Complied',
            'not_complied' => 'Not Complied',
            'not_applicable' => 'Not Applicable',
            default => 'Pending',
        };
    }
}
