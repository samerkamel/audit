<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use App\Models\CheckListGroup;
use App\Notifications\AuditScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditPlanController extends Controller
{
    /**
     * Display a listing of audit plans.
     */
    public function index(Request $request)
    {
        $query = AuditPlan::with(['departments', 'leadAuditor', 'creator']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('scope', 'like', "%{$search}%");
            });
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
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'quality_manager', 'quality_engineer', 'management_rep', 'external_auditor']);
        })->get();

        // Calculate statistics
        $stats = [
            'total' => AuditPlan::count(),
            'draft' => AuditPlan::where('status', 'draft')->count(),
            'planned' => AuditPlan::where('status', 'planned')->count(),
            'in_progress' => AuditPlan::where('status', 'in_progress')->count(),
            'completed' => AuditPlan::where('status', 'completed')->count(),
            'overdue' => AuditPlan::whereHas('departments', function ($query) {
                $query->where('planned_end_date', '<', now())
                    ->whereNotIn('audit_plan_department.status', ['completed', 'deferred']);
            })->count(),
        ];

        // Prepare Gantt chart data
        $ganttData = $auditPlans->map(function($plan) {
            $startDate = $plan->departments->min('pivot.planned_start_date') ?? $plan->actual_start_date;
            $endDate = $plan->departments->max('pivot.planned_end_date') ?? $plan->actual_end_date;

            $color = match($plan->status) {
                'draft' => '#6c757d',
                'planned' => '#17a2b8',
                'in_progress' => '#ffc107',
                'completed' => '#28a745',
                'cancelled' => '#dc3545',
                default => '#6c757d'
            };

            if ($plan->isOverdue()) {
                $color = '#dc3545';
            }

            return [
                'x' => $plan->title,
                'y' => [
                    $startDate ? strtotime($startDate) * 1000 : null,
                    $endDate ? strtotime($endDate) * 1000 : null
                ],
                'fillColor' => $color,
                'id' => $plan->id,
                'status' => $plan->status
            ];
        })->filter(function($item) {
            return $item['y'][0] !== null && $item['y'][1] !== null;
        })->values();

        return view('audit-plans.index', compact('auditPlans', 'departments', 'auditors', 'stats', 'ganttData'));
    }

    /**
     * Show the form for creating a new audit plan.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'quality_manager', 'quality_engineer', 'management_rep', 'external_auditor']);
        })->get();
        $checklistGroups = CheckListGroup::where('is_active', true)
            ->with('auditQuestions')
            ->orderBy('display_order')
            ->orderBy('code')
            ->get();

        return view('audit-plans.create', compact('departments', 'auditors', 'checklistGroups'));
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
            'departments' => ['required', 'array', 'min:1'],
            'departments.*.department_id' => ['required', 'exists:departments,id'],
            'departments.*.auditor_ids' => ['nullable', 'array'],
            'departments.*.auditor_ids.*' => ['exists:users,id'],
            'departments.*.checklist_group_ids' => ['nullable', 'array'],
            'departments.*.checklist_group_ids.*' => ['exists:check_list_groups,id'],
            'departments.*.planned_start_date' => ['nullable', 'date'],
            'departments.*.planned_end_date' => ['nullable', 'date'],
            'departments.*.notes' => ['nullable', 'string'],
            'lead_auditor_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:draft,planned,in_progress,completed,cancelled'],
            'is_active' => ['boolean'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Extract departments data before creating audit plan
        $departmentsData = $validated['departments'];
        unset($validated['departments']);

        $auditPlan = AuditPlan::create($validated);

        // Attach departments with their specific data
        foreach ($departmentsData as $deptData) {
            $departmentId = $deptData['department_id'];

            // Attach department to audit plan
            $auditPlan->departments()->attach($departmentId, [
                'status' => 'pending',
                'planned_start_date' => $deptData['planned_start_date'] ?? null,
                'planned_end_date' => $deptData['planned_end_date'] ?? null,
                'notes' => $deptData['notes'] ?? null,
            ]);

            // Get the pivot record we just created
            $pivotRecord = DB::table('audit_plan_department')
                ->where('audit_plan_id', $auditPlan->id)
                ->where('department_id', $departmentId)
                ->first();

            // Attach auditors to this department if any
            if (!empty($deptData['auditor_ids']) && $pivotRecord) {
                foreach ($deptData['auditor_ids'] as $auditorId) {
                    DB::table('audit_plan_department_auditor')->insert([
                        'audit_plan_department_id' => $pivotRecord->id,
                        'user_id' => $auditorId,
                        'role' => 'member',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach checklist groups to this department
            if (!empty($deptData['checklist_group_ids'])) {
                foreach ($deptData['checklist_group_ids'] as $checklistGroupId) {
                    DB::table('audit_plan_checklist_groups')->insert([
                        'audit_plan_id' => $auditPlan->id,
                        'department_id' => $departmentId,
                        'checklist_group_id' => $checklistGroupId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('audit-plans.index')
            ->with('success', 'Audit plan created successfully.');
    }

    /**
     * Display the specified audit plan.
     */
    public function show(AuditPlan $auditPlan)
    {
        $auditPlan->load(['departments', 'leadAuditor', 'creator']);

        // Calculate audit plan statistics based on department dates
        $departmentDates = $auditPlan->departments()
            ->select('planned_start_date', 'planned_end_date')
            ->get();

        $earliestStart = $departmentDates->min('planned_start_date');
        $latestEnd = $departmentDates->max('planned_end_date');

        $stats = [];
        if ($earliestStart && $latestEnd) {
            $stats['duration'] = \Carbon\Carbon::parse($earliestStart)->diffInDays(\Carbon\Carbon::parse($latestEnd));
            $stats['days_remaining'] = \Carbon\Carbon::parse($latestEnd)->diffInDays(now(), false);
            $stats['is_overdue'] = !in_array($auditPlan->status, ['completed', 'cancelled'])
                && \Carbon\Carbon::parse($latestEnd) < now()
                && !$auditPlan->actual_end_date;
        }

        return view('audit-plans.show', compact('auditPlan', 'stats'));
    }

    /**
     * Show the form for editing the specified audit plan.
     */
    public function edit(AuditPlan $auditPlan)
    {
        $departments = Department::where('is_active', true)->get();
        $auditors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'quality_manager', 'quality_engineer', 'management_rep', 'external_auditor']);
        })->get();
        $checklistGroups = CheckListGroup::where('is_active', true)
            ->with('auditQuestions')
            ->orderBy('display_order')
            ->orderBy('code')
            ->get();

        // Load existing checklist group assignments per department
        $auditPlan->load('checklistGroups');

        return view('audit-plans.edit', compact('auditPlan', 'departments', 'auditors', 'checklistGroups'));
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
            'departments' => ['required', 'array', 'min:1'],
            'departments.*.department_id' => ['required', 'exists:departments,id'],
            'departments.*.auditor_ids' => ['nullable', 'array'],
            'departments.*.auditor_ids.*' => ['exists:users,id'],
            'departments.*.checklist_group_ids' => ['nullable', 'array'],
            'departments.*.checklist_group_ids.*' => ['exists:check_list_groups,id'],
            'departments.*.planned_start_date' => ['nullable', 'date'],
            'departments.*.planned_end_date' => ['nullable', 'date'],
            'departments.*.notes' => ['nullable', 'string'],
            'lead_auditor_id' => ['required', 'exists:users,id'],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after:actual_start_date'],
            'status' => ['required', 'in:draft,planned,in_progress,completed,cancelled'],
            'is_active' => ['boolean'],
        ]);

        // Extract departments data before updating audit plan
        $departmentsData = $validated['departments'];
        unset($validated['departments']);

        $auditPlan->update($validated);

        // Sync departments - detach all first, then reattach with new data
        $auditPlan->departments()->detach();

        // Also delete existing checklist group assignments
        DB::table('audit_plan_checklist_groups')
            ->where('audit_plan_id', $auditPlan->id)
            ->delete();

        // Attach departments with their specific data
        foreach ($departmentsData as $deptData) {
            $departmentId = $deptData['department_id'];

            // Attach department to audit plan
            $auditPlan->departments()->attach($departmentId, [
                'status' => 'pending',
                'planned_start_date' => $deptData['planned_start_date'] ?? null,
                'planned_end_date' => $deptData['planned_end_date'] ?? null,
                'notes' => $deptData['notes'] ?? null,
            ]);

            // Get the pivot record we just created
            $pivotRecord = DB::table('audit_plan_department')
                ->where('audit_plan_id', $auditPlan->id)
                ->where('department_id', $departmentId)
                ->first();

            // Attach auditors to this department if any
            if (!empty($deptData['auditor_ids']) && $pivotRecord) {
                foreach ($deptData['auditor_ids'] as $auditorId) {
                    DB::table('audit_plan_department_auditor')->insert([
                        'audit_plan_department_id' => $pivotRecord->id,
                        'user_id' => $auditorId,
                        'role' => 'member',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach checklist groups to this department
            if (!empty($deptData['checklist_group_ids'])) {
                foreach ($deptData['checklist_group_ids'] as $checklistGroupId) {
                    DB::table('audit_plan_checklist_groups')->insert([
                        'audit_plan_id' => $auditPlan->id,
                        'department_id' => $departmentId,
                        'checklist_group_id' => $checklistGroupId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

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

        // Send notifications to lead auditor and department users
        $this->sendAuditNotifications($auditPlan);

        return redirect()->route('audit-plans.show', $auditPlan)
            ->with('success', 'Audit plan started successfully.');
    }

    /**
     * Send audit notifications to relevant users.
     */
    protected function sendAuditNotifications(AuditPlan $auditPlan): void
    {
        // Notify lead auditor
        if ($auditPlan->leadAuditor) {
            $auditPlan->leadAuditor->notify(new AuditScheduledNotification($auditPlan));
        }

        // Notify department users
        foreach ($auditPlan->departments as $department) {
            $users = User::where('department_id', $department->id)
                ->where('is_active', true)
                ->get();

            foreach ($users as $user) {
                $user->notify(new AuditScheduledNotification($auditPlan, $department));
            }
        }
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
