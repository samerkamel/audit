<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AuditQuestion extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'question_text',
        'iso_reference',
        'quality_procedure_reference',
        'category',
        'description',
        'is_required',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * The checklist groups that include this question.
     */
    public function checklistGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            CheckListGroup::class,
            'checklist_group_question',
            'audit_question_id',    // Foreign pivot key
            'checklist_group_id'    // Related pivot key
        )
            ->withPivot('display_order')
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the category badge color.
     */
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'compliance' => 'primary',
            'operational' => 'info',
            'financial' => 'success',
            'it' => 'warning',
            'quality' => 'secondary',
            'security' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'compliance' => 'Compliance',
            'operational' => 'Operational',
            'financial' => 'Financial',
            'it' => 'IT',
            'quality' => 'Quality',
            'security' => 'Security',
            default => ucfirst($this->category),
        };
    }
}
