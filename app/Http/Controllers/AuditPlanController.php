<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditPlanController extends Controller
{
    /**
     * Display a listing of audit plans.
     */
    public function index(Request $request)
    {
        $query = AuditPlan::with(['sector', 'departments', 'leadAuditor', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('scope', 'like', "%{$search}%");
            });
        }

        // Filter by sector
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('departments.id', $request->department_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by audit type
        if ($request->filled('audit_type')) {
            $query->where('audit_type', $request->audit_type);
        }

        // Filter by lead auditor
        if ($request->filled('lead_auditor_id')) {
            $query->where('lead_auditor_id', $request->lead_auditor_id);
        }

        $auditPlans = $query->latest()->paginate(15);
        $sectors = Sector::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'auditor', 'lead_auditor', 'manager']);
        })->get();

        // Calculate statistics
        $stats = [
            'total' => AuditPlan::count(),
            'draft' => AuditPlan::where('status', 'draft')->count(),
            'planned' => AuditPlan::where('status', 'planned')->count(),
            'in_progress' => AuditPlan::where('status', 'in_progress')->count(),
            'completed' => AuditPlan::where('status', 'completed')->count(),
            'overdue' => AuditPlan::whereIn('status', ['planned', 'in_progress'])
                ->where('planned_end_date', '<', now())
                ->whereNull('actual_end_date')
                ->count(),
        ];

        return view('audit-plans.index', compact('auditPlans', 'sectors', 'departments', 'auditors', 'stats'));
    }

    /**
     * Show the form for creating a new audit plan.
     */
    public function create()
    {
        $sectors = Sector::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'auditor', 'lead_auditor', 'manager']);
        })->get();

        return view('audit-plans.create', compact('sectors', 'departments', 'auditors'));
    }

    /**
     * Store a newly created audit plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'audit_type' => ['required', 'in:internal,external,compliance,operational,financial,it,quality'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'sector_id' => ['required', 'exists:sectors,id'],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['exists:departments,id'],
            'lead_auditor_id' => ['required', 'exists:users,id'],
            'planned_start_date' => ['required', 'date', 'after_or_equal:today'],
            'planned_end_date' => ['required', 'date', 'after:planned_start_date'],
            'status' => ['required', 'in:draft,planned,in_progress,completed,cancelled'],
            'is_active' => ['boolean'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Extract department_ids before creating audit plan
        $departmentIds = $validated['department_ids'];
        unset($validated['department_ids']);

        $auditPlan = AuditPlan::create($validated);

        // Attach departments with default status
        $departmentData = [];
        foreach ($departmentIds as $departmentId) {
            $departmentData[$departmentId] = [
                'status' => 'pending',
                'planned_start_date' => $validated['planned_start_date'],
                'planned_end_date' => $validated['planned_end_date'],
            ];
        }
        $auditPlan->departments()->attach($departmentData);

        return redirect()->route('audit-plans.index')
            ->with('success', 'Audit plan created successfully.');
    }

    /**
     * Display the specified audit plan.
     */
    public function show(AuditPlan $auditPlan)
    {
        $auditPlan->load(['sector', 'departments', 'leadAuditor', 'creator']);

        // Calculate audit plan statistics
        $stats = [
            'duration' => $auditPlan->duration,
            'days_remaining' => $auditPlan->planned_end_date->diffInDays(now(), false),
            'is_overdue' => $auditPlan->isOverdue(),
        ];

        return view('audit-plans.show', compact('auditPlan', 'stats'));
    }

    /**
     * Show the form for editing the specified audit plan.
     */
    public function edit(AuditPlan $auditPlan)
    {
        $sectors = Sector::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'auditor', 'lead_auditor', 'manager']);
        })->get();

        return view('audit-plans.edit', compact('auditPlan', 'sectors', 'departments', 'auditors'));
    }

    /**
     * Update the specified audit plan.
     */
    public function update(Request $request, AuditPlan $auditPlan)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'audit_type' => ['required', 'in:internal,external,compliance,operational,financial,it,quality'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'sector_id' => ['required', 'exists:sectors,id'],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['exists:departments,id'],
            'lead_auditor_id' => ['required', 'exists:users,id'],
            'planned_start_date' => ['required', 'date'],
            'planned_end_date' => ['required', 'date', 'after:planned_start_date'],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after:actual_start_date'],
            'status' => ['required', 'in:draft,planned,in_progress,completed,cancelled'],
            'is_active' => ['boolean'],
        ]);

        // Extract department_ids before updating audit plan
        $departmentIds = $validated['department_ids'];
        unset($validated['department_ids']);

        $auditPlan->update($validated);

        // Sync departments - preserve existing pivot data for unchanged departments
        $departmentData = [];
        foreach ($departmentIds as $departmentId) {
            // If department already exists in audit plan, preserve its data
            $existingPivot = $auditPlan->departments()->where('department_id', $departmentId)->first();
            if ($existingPivot) {
                $departmentData[$departmentId] = [
                    'status' => $existingPivot->pivot->status,
                    'planned_start_date' => $existingPivot->pivot->planned_start_date,
                    'planned_end_date' => $existingPivot->pivot->planned_end_date,
                    'actual_start_date' => $existingPivot->pivot->actual_start_date,
                    'actual_end_date' => $existingPivot->pivot->actual_end_date,
                    'notes' => $existingPivot->pivot->notes,
                ];
            } else {
                // New department, use default values
                $departmentData[$departmentId] = [
                    'status' => 'pending',
                    'planned_start_date' => $validated['planned_start_date'],
                    'planned_end_date' => $validated['planned_end_date'],
                ];
            }
        }
        $auditPlan->departments()->sync($departmentData);

        return redirect()->route('audit-plans.index')
            ->with('success', 'Audit plan updated successfully.');
    }

    /**
     * Soft delete the specified audit plan.
     */
    public function destroy(AuditPlan $auditPlan)
    {
        // Check if audit plan can be deleted (e.g., not in progress)
        if ($auditPlan->status === 'in_progress') {
            return redirect()->route('audit-plans.index')
                ->with('error', 'Cannot delete audit plan that is currently in progress.');
        }

        $auditPlan->delete();

        return redirect()->route('audit-plans.index')
            ->with('success', 'Audit plan deleted successfully.');
    }

    /**
     * Start an audit plan (change status to in_progress).
     */
    public function start(AuditPlan $auditPlan)
    {
        if ($auditPlan->status !== 'planned') {
            return redirect()->route('audit-plans.show', $auditPlan)
                ->with('error', 'Only planned audit plans can be started.');
        }

        $auditPlan->update([
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);

        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Audit plan started successfully.');
    }

    /**
     * Complete an audit plan (change status to completed).
     */
    public function complete(AuditPlan $auditPlan)
    {
        if ($auditPlan->status !== 'in_progress') {
            return redirect()->route('audit-plans.show', $auditPlan)
                ->with('error', 'Only in-progress audit plans can be completed.');
        }

        $auditPlan->update([
            'status' => 'completed',
            'actual_end_date' => now(),
        ]);

        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Audit plan completed successfully.');
    }

    /**
     * Cancel an audit plan.
     */
    public function cancel(AuditPlan $auditPlan)
    {
        if (in_array($auditPlan->status, ['completed', 'cancelled'])) {
            return redirect()->route('audit-plans.show', $auditPlan)
                ->with('error', 'Audit plan is already completed or cancelled.');
        }

        $auditPlan->update(['status' => 'cancelled']);

        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Audit plan cancelled successfully.');
    }
}
