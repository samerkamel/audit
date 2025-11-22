<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_number',
        'complaint_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'complaint_subject',
        'complaint_description',
        'complaint_category',
        'priority',
        'severity',
        'assigned_to_department_id',
        'assigned_to_user_id',
        'initial_response',
        'response_date',
        'root_cause_analysis',
        'corrective_action',
        'resolution',
        'resolved_date',
        'status',
        'car_required',
        'car_id',
        'received_by',
        'resolved_by',
        'closed_by',
        'closed_at',
        'satisfaction_rating',
        'customer_feedback',
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'response_date' => 'date',
        'resolved_date' => 'date',
        'closed_at' => 'datetime',
        'car_required' => 'boolean',
        'satisfaction_rating' => 'integer',
    ];

    /**
     * Generate complaint number in format: COMP-25-0001
     */
    public static function generateComplaintNumber(): string
    {
        $year = date('y');
        $prefix = "COMP-{$year}-";

        // Include soft-deleted records to avoid duplicate key errors
        $lastComplaint = static::withTrashed()
            ->where('complaint_number', 'like', "{$prefix}%")
            ->orderBy('complaint_number', 'desc')
            ->first();

        if ($lastComplaint) {
            $lastNumber = (int) substr($lastComplaint->complaint_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function assignedToDepartment()
    {
        return $this->belongsTo(Department::class, 'assigned_to_department_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Helper Methods
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'acknowledged' => 'info',
            'investigating' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'escalated' => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'info',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'minor' => 'info',
            'major' => 'warning',
            'critical' => 'danger',
            default => 'secondary',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->complaint_category) {
            'product_quality' => 'Product Quality',
            'service_quality' => 'Service Quality',
            'delivery' => 'Delivery',
            'documentation' => 'Documentation',
            'technical_support' => 'Technical Support',
            'billing' => 'Billing',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->complaint_category)),
        };
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'closed' || !$this->response_date) {
            return false;
        }

        return now()->greaterThan($this->response_date) &&
               !in_array($this->status, ['resolved', 'closed']);
    }

    public function canGenerateCar(): bool
    {
        return $this->car_required &&
               !$this->car_id &&
               in_array($this->status, ['investigating', 'resolved']);
    }

    public function canBeClosed(): bool
    {
        return $this->status === 'resolved' &&
               (!$this->car_required || $this->car_id);
    }
}
