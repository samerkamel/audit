<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'quality_procedure_reference',
        'iso_clause_reference',
        'department_id',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department this procedure belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the auditors recommended for this procedure.
     */
    public function recommendedAuditors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'auditor_procedure_recommendations', 'procedure_id', 'user_id')
            ->withPivot(['recommendation_level', 'notes', 'is_certified', 'certification_date', 'certification_expiry'])
            ->withTimestamps();
    }

    /**
     * Get primary auditors for this procedure.
     */
    public function primaryAuditors(): BelongsToMany
    {
        return $this->recommendedAuditors()->wherePivot('recommendation_level', 'primary');
    }

    /**
     * Get secondary auditors for this procedure.
     */
    public function secondaryAuditors(): BelongsToMany
    {
        return $this->recommendedAuditors()->wherePivot('recommendation_level', 'secondary');
    }

    /**
     * Get backup auditors for this procedure.
     */
    public function backupAuditors(): BelongsToMany
    {
        return $this->recommendedAuditors()->wherePivot('recommendation_level', 'backup');
    }

    /**
     * Scope for active procedures.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for procedures by department.
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
