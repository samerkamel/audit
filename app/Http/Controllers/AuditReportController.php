<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditResponse;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditReportController extends Controller
{
    /**
     * Display a list of audit plans with reporting data.
     */
    public function index(Request $request)
    {
        $query = AuditPlan::with(['departments', 'leadAuditor'])
            ->where('is_active', true);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by audit type
        if ($request->filled('audit_type')) {
            $query->where('audit_type', $request->audit_type);
        }

        $auditPlans = $query->latest()->paginate(15);

        // Calculate statistics for each audit plan
        foreach ($auditPlans as $plan) {
            $totalResponses = AuditResponse::where('audit_plan_id', $plan->id)->count();
            $complied = AuditResponse::where('audit_plan_id', $plan->id)
                ->where('response', 'complied')
                ->count();
            $notComplied = AuditResponse::where('audit_plan_id', $plan->id)
                ->where('response', 'not_complied')
                ->count();
            $notApplicable = AuditResponse::where('audit_plan_id', $plan->id)
                ->where('response', 'not_applicable')
                ->count();

            $plan->total_responses = $totalResponses;
            $plan->complied_count = $complied;
            $plan->not_complied_count = $notComplied;
            $plan->not_applicable_count = $notApplicable;

            // Calculate compliance percentage (excluding not applicable)
            $applicableTotal = $complied + $notComplied;
            $plan->compliance_percentage = $applicableTotal > 0
                ? round(($complied / $applicableTotal) * 100, 1)
                : 0;
        }

        return view('audit-reports.index', compact('auditPlans'));
    }

    /**
     * Show detailed report for a specific audit plan.
     */
    public function show(AuditPlan $auditPlan)
    {
        $auditPlan->load(['departments', 'leadAuditor', 'creator']);

        // Overall statistics
        $totalResponses = AuditResponse::where('audit_plan_id', $auditPlan->id)->count();
        $complied = AuditResponse::where('audit_plan_id', $auditPlan->id)
            ->where('response', 'complied')
            ->count();
        $notComplied = AuditResponse::where('audit_plan_id', $auditPlan->id)
            ->where('response', 'not_complied')
            ->count();
        $notApplicable = AuditResponse::where('audit_plan_id', $auditPlan->id)
            ->where('response', 'not_applicable')
            ->count();

        $applicableTotal = $complied + $notComplied;
        $compliancePercentage = $applicableTotal > 0
            ? round(($complied / $applicableTotal) * 100, 1)
            : 0;

        $stats = [
            'total_responses' => $totalResponses,
            'complied' => $complied,
            'not_complied' => $notComplied,
            'not_applicable' => $notApplicable,
            'compliance_percentage' => $compliancePercentage,
        ];

        // Department-wise statistics
        $departmentStats = [];
        foreach ($auditPlan->departments as $department) {
            $deptResponses = AuditResponse::where('audit_plan_id', $auditPlan->id)
                ->where('department_id', $department->id);

            $deptComplied = (clone $deptResponses)->where('response', 'complied')->count();
            $deptNotComplied = (clone $deptResponses)->where('response', 'not_complied')->count();
            $deptNotApplicable = (clone $deptResponses)->where('response', 'not_applicable')->count();
            $deptTotal = $deptResponses->count();

            $deptApplicableTotal = $deptComplied + $deptNotComplied;
            $deptCompliancePercentage = $deptApplicableTotal > 0
                ? round(($deptComplied / $deptApplicableTotal) * 100, 1)
                : 0;

            $departmentStats[$department->id] = [
                'department' => $department,
                'total' => $deptTotal,
                'complied' => $deptComplied,
                'not_complied' => $deptNotComplied,
                'not_applicable' => $deptNotApplicable,
                'compliance_percentage' => $deptCompliancePercentage,
            ];
        }

        // Get all findings (non-compliances)
        $findings = AuditResponse::where('audit_plan_id', $auditPlan->id)
            ->where('response', 'not_complied')
            ->with(['department', 'checklistGroup', 'auditQuestion', 'auditor'])
            ->orderBy('department_id')
            ->orderBy('checklist_group_id')
            ->get();

        return view('audit-reports.show', compact(
            'auditPlan',
            'stats',
            'departmentStats',
            'findings'
        ));
    }

    /**
     * Show detailed department report.
     */
    public function department(AuditPlan $auditPlan, Department $department)
    {
        $auditPlan->load(['leadAuditor']);

        // Get checklist groups for this department
        $checklistGroups = $auditPlan->checklistGroupsForDepartment($department->id)->get();

        $groupStats = [];
        foreach ($checklistGroups as $group) {
            $groupResponses = AuditResponse::where('audit_plan_id', $auditPlan->id)
                ->where('department_id', $department->id)
                ->where('checklist_group_id', $group->id);

            $groupComplied = (clone $groupResponses)->where('response', 'complied')->count();
            $groupNotComplied = (clone $groupResponses)->where('response', 'not_complied')->count();
            $groupNotApplicable = (clone $groupResponses)->where('response', 'not_applicable')->count();
            $groupTotal = $groupResponses->count();

            $groupApplicableTotal = $groupComplied + $groupNotComplied;
            $groupCompliancePercentage = $groupApplicableTotal > 0
                ? round(($groupComplied / $groupApplicableTotal) * 100, 1)
                : 0;

            // Get all responses for this group with questions
            $responses = AuditResponse::where('audit_plan_id', $auditPlan->id)
                ->where('department_id', $department->id)
                ->where('checklist_group_id', $group->id)
                ->with(['auditQuestion', 'auditor'])
                ->get();

            $groupStats[] = [
                'group' => $group,
                'total' => $groupTotal,
                'complied' => $groupComplied,
                'not_complied' => $groupNotComplied,
                'not_applicable' => $groupNotApplicable,
                'compliance_percentage' => $groupCompliancePercentage,
                'responses' => $responses,
            ];
        }

        return view('audit-reports.department', compact(
            'auditPlan',
            'department',
            'groupStats'
        ));
    }
}
