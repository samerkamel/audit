<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'title',
        'category',
        'version',
        'revision_number',
        'effective_date',
        'next_review_date',
        'status',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'owner_id',
        'reviewed_by',
        'approved_by',
        'reviewed_date',
        'approved_date',
        'applicable_departments',
        'related_documents',
        'keywords',
        'revision_notes',
        'supersedes_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'next_review_date' => 'date',
        'reviewed_date' => 'date',
        'approved_date' => 'date',
        'applicable_departments' => 'array',
        'related_documents' => 'array',
        'keywords' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function supersedes()
    {
        return $this->belongsTo(Document::class, 'supersedes_id');
    }

    public function supersededBy()
    {
        return $this->hasMany(Document::class, 'supersedes_id');
    }

    // Auto-generate document number
    public static function generateDocumentNumber(string $category): string
    {
        $prefix = match ($category) {
            'quality_manual' => 'QM',
            'procedure' => 'PROC',
            'work_instruction' => 'WI',
            'form' => 'FORM',
            'record' => 'REC',
            'external_document' => 'EXT',
            default => 'DOC',
        };

        $year = date('Y');
        // Include soft-deleted records to avoid duplicate key errors
        $lastDocument = static::withTrashed()
            ->where('document_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('document_number', 'desc')
            ->first();

        $newNumber = $lastDocument
            ? ((int) substr($lastDocument->document_number, -4)) + 1
            : 1;

        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }

    // Increment version
    public function incrementVersion(): string
    {
        $parts = explode('.', $this->version);
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);

        // Major version increment for approved documents
        if ($this->status === 'effective' || $this->status === 'approved') {
            $major++;
            $minor = 0;
        } else {
            // Minor version increment for drafts
            $minor++;
        }

        return "{$major}.{$minor}";
    }

    // Status checks
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isEffective(): bool
    {
        return $this->status === 'effective';
    }

    public function isObsolete(): bool
    {
        return $this->status === 'obsolete';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending_review']);
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'pending_review';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function needsReview(): bool
    {
        if (!$this->next_review_date) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->next_review_date);
    }

    // Workflow methods
    public function submitForReview(): bool
    {
        if (!$this->isDraft()) {
            return false;
        }

        $this->update(['status' => 'pending_review']);
        return true;
    }

    public function review(int $reviewerId): bool
    {
        if (!$this->canBeReviewed()) {
            return false;
        }

        $this->update([
            'status' => 'pending_approval',
            'reviewed_by' => $reviewerId,
            'reviewed_date' => now(),
        ]);

        return true;
    }

    public function approve(int $approverId): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_date' => now(),
        ]);

        return true;
    }

    public function makeEffective(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        // If superseding another document, make the old one obsolete
        if ($this->supersedes_id) {
            $oldDocument = Document::find($this->supersedes_id);
            if ($oldDocument) {
                $oldDocument->update(['status' => 'obsolete']);
            }
        }

        $this->update([
            'status' => 'effective',
            'effective_date' => now(),
            'next_review_date' => now()->addYear(), // Default 1-year review cycle
        ]);

        return true;
    }

    public function makeObsolete(): bool
    {
        if (!in_array($this->status, ['effective', 'approved'])) {
            return false;
        }

        $this->update(['status' => 'obsolete']);
        return true;
    }

    public function archive(): bool
    {
        if ($this->status !== 'obsolete') {
            return false;
        }

        $this->update(['status' => 'archived']);
        return true;
    }

    // Helper methods
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'quality_manual' => 'Quality Manual',
            'procedure' => 'Procedure',
            'work_instruction' => 'Work Instruction',
            'form' => 'Form',
            'record' => 'Record',
            'external_document' => 'External Document',
            default => ucfirst($this->category),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'pending_review' => 'info',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'effective' => 'primary',
            'obsolete' => 'danger',
            'archived' => 'dark',
            default => 'secondary',
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function getDaysUntilReviewAttribute(): ?int
    {
        if (!$this->next_review_date) {
            return null;
        }

        return now()->diffInDays($this->next_review_date, false);
    }

    // Scopes
    public function scopeEffective($query)
    {
        return $query->where('status', 'effective');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNeedingReview($query)
    {
        return $query->where('status', 'effective')
            ->whereDate('next_review_date', '<=', now());
    }
}
