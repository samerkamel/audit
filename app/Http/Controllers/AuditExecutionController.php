<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditResponse;
use App\Models\Department;
use App\Models\CheckListGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditExecutionController extends Controller
{
    /**
     * Display a list of audit plans assigned to the current auditor.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get audit plans where user is lead auditor or assigned as team member
        $query = AuditPlan::with(['departments', 'leadAuditor', 'creator'])
            ->where(function ($q) use ($user) {
                // Lead auditor
                $q->where('lead_auditor_id', $user->id)
                    // Or assigned to any department in this audit plan
                    ->orWhereExists(function ($subquery) use ($user) {
                        $subquery->select(DB::raw(1))
                            ->from('audit_plan_department')
                            ->join('audit_plan_department_auditor', 'audit_plan_department.id', '=', 'audit_plan_department_auditor.audit_plan_department_id')
                            ->whereColumn('audit_plan_department.audit_plan_id', 'audit_plans.id')
                            ->where('audit_plan_department_auditor.user_id', $user->id);
                    });
            })
            ->where('is_active', true)
            ->whereIn('status', ['planned', 'in_progress']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $auditPlans = $query->latest()->paginate(15);

        // Calculate progress for each audit plan
        foreach ($auditPlans as $plan) {
            $totalQuestions = 0;
            $answeredQuestions = 0;

            foreach ($plan->departments as $department) {
                $checklistGroups = $plan->checklistGroupsForDepartment($department->id)->get();
                foreach ($checklistGroups as $group) {
                    $questionIds = $group->auditQuestions->pluck('id');
                    $totalQuestions += $questionIds->count();

                    $answered = AuditResponse::where('audit_plan_id', $plan->id)
                        ->where('department_id', $department->id)
                        ->whereIn('audit_question_id', $questionIds)
                        ->count();

                    $answeredQuestions += $answered;
                }
            }

            $plan->progress_percentage = $totalQuestions > 0
                ? round(($answeredQuestions / $totalQuestions) * 100, 1)
                : 0;
        }

        return view('audit-execution.index', compact('auditPlans'));
    }

    /**
     * Show the audit execution dashboard for a specific audit plan.
     */
    public function show(AuditPlan $auditPlan)
    {
        $auditPlan->load(['departments', 'leadAuditor']);

        // Check if user has access (lead auditor or assigned to any department)
        $user = Auth::user();
        $isLeadAuditor = $auditPlan->lead_auditor_id === $user->id;

        // Check if user is assigned to any department in this audit plan
        $isAssignedAuditor = DB::table('audit_plan_department')
            ->join('audit_plan_department_auditor', 'audit_plan_department.id', '=', 'audit_plan_department_auditor.audit_plan_department_id')
            ->where('audit_plan_department.audit_plan_id', $auditPlan->id)
            ->where('audit_plan_department_auditor.user_id', $user->id)
            ->exists();

        if (!$isLeadAuditor && !$isAssignedAuditor) {
            return redirect()->route('audit-execution.index')
                ->with('error', 'You do not have access to this audit plan.');
        }

        // Get departments with their checklist groups and progress
        $departments = $auditPlan->departments->map(function ($department) use ($auditPlan) {
            $checklistGroups = $auditPlan->checklistGroupsForDepartment($department->id)->get();

            $department->checklist_groups = $checklistGroups->map(function ($group) use ($auditPlan, $department) {
                $questionIds = $group->auditQuestions->pluck('id');
                $totalQuestions = $questionIds->count();

                $answeredQuestions = AuditResponse::where('audit_plan_id', $auditPlan->id)
                    ->where('department_id', $department->id)
                    ->where('checklist_group_id', $group->id)
                    ->whereIn('audit_question_id', $questionIds)
                    ->count();

                $group->total_questions = $totalQuestions;
                $group->answered_questions = $answeredQuestions;
                $group->progress_percentage = $totalQuestions > 0
                    ? round(($answeredQuestions / $totalQuestions) * 100, 1)
                    : 0;

                return $group;
            });

            return $department;
        });

        return view('audit-execution.show', compact('auditPlan', 'departments'));
    }

    /**
     * Show the audit execution form for a specific department and checklist group.
     */
    public function execute(AuditPlan $auditPlan, Department $department, CheckListGroup $checklistGroup)
    {
        // Check if user has access (lead auditor or assigned to this department)
        $user = Auth::user();
        $isLeadAuditor = $auditPlan->lead_auditor_id === $user->id;

        // Check if user is assigned to this specific department in this audit plan
        $isAssignedToDepartment = DB::table('audit_plan_department')
            ->join('audit_plan_department_auditor', 'audit_plan_department.id', '=', 'audit_plan_department_auditor.audit_plan_department_id')
            ->where('audit_plan_department.audit_plan_id', $auditPlan->id)
            ->where('audit_plan_department.department_id', $department->id)
            ->where('audit_plan_department_auditor.user_id', $user->id)
            ->exists();

        if (!$isLeadAuditor && !$isAssignedToDepartment) {
            return redirect()->route('audit-execution.show', $auditPlan)
                ->with('error', 'You do not have access to audit this department.');
        }

        // Load questions for this checklist group
        $questions = $checklistGroup->auditQuestions()
            ->orderByPivot('display_order')
            ->get();

        // Load existing responses
        $existingResponses = AuditResponse::where('audit_plan_id', $auditPlan->id)
            ->where('department_id', $department->id)
            ->where('checklist_group_id', $checklistGroup->id)
            ->get()
            ->keyBy('audit_question_id');

        return view('audit-execution.execute', compact(
            'auditPlan',
            'department',
            'checklistGroup',
            'questions',
            'existingResponses'
        ));
    }

    /**
     * Store or update audit responses.
     */
    public function store(Request $request, AuditPlan $auditPlan, Department $department, CheckListGroup $checklistGroup)
    {
        $validated = $request->validate([
            'responses' => ['required', 'array'],
            'responses.*.question_id' => ['required', 'exists:audit_questions,id'],
            'responses.*.response' => ['required', 'in:complied,not_complied,not_applicable'],
            'responses.*.comments' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['responses'] as $responseData) {
                AuditResponse::updateOrCreate(
                    [
                        'audit_plan_id' => $auditPlan->id,
                        'department_id' => $department->id,
                        'audit_question_id' => $responseData['question_id'],
                    ],
                    [
                        'checklist_group_id' => $checklistGroup->id,
                        'auditor_id' => Auth::id(),
                        'response' => $responseData['response'],
                        'comments' => $responseData['comments'] ?? null,
                        'audited_at' => now(),
                    ]
                );
            }

            DB::commit();

            return redirect()->route('audit-execution.show', $auditPlan)
                ->with('success', 'Audit responses saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to save audit responses. Please try again.')
                ->withInput();
        }
    }
}
