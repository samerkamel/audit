<?php

namespace App\Http\Controllers;

use App\Models\ImprovementOpportunity;
use App\Models\AuditResponse;
use App\Models\Department;
use App\Models\User;
use App\Notifications\ImprovementOpportunityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImprovementOpportunityController extends Controller
{
    /**
     * Display a listing of Improvement Opportunities.
     */
    public function index(Request $request)
    {
        $query = ImprovementOpportunity::with([
            'fromDepartment',
            'toDepartment',
            'issuedBy',
            'approvedBy',
            'latestResponse',
            'auditFinding.audit.auditPlan'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        if ($request->filled('department')) {
            $query->where('to_department_id', $request->department);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('io_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('observation_description', 'like', "%{$search}%");
            });
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $improvementOpportunities = $query->paginate(15);

        // Statistics for dashboard cards
        $statistics = [
            'total' => ImprovementOpportunity::count(),
            'draft' => ImprovementOpportunity::where('status', 'draft')->count(),
            'pending_approval' => ImprovementOpportunity::where('status', 'pending_approval')->count(),
            'issued' => ImprovementOpportunity::where('status', 'issued')->count(),
            'in_progress' => ImprovementOpportunity::where('status', 'in_progress')->count(),
            'pending_review' => ImprovementOpportunity::where('status', 'pending_review')->count(),
            'closed' => ImprovementOpportunity::where('status', 'closed')->count(),
            'high_priority' => ImprovementOpportunity::where('priority', 'high')->count(),
            'overdue' => ImprovementOpportunity::overdue()->count(),
        ];

        $departments = Department::orderBy('name')->get();

        return view('improvement-opportunities.index', compact('improvementOpportunities', 'statistics', 'departments'));
    }

    /**
     * Show the form for creating a new Improvement Opportunity.
     */
    public function create(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // If coming from an audit finding (observation), pre-populate
        $auditFinding = null;
        if ($request->filled('audit_finding_id')) {
            $auditFinding = AuditResponse::with([
                'audit.auditPlan.department',
                'audit.auditee'
            ])->findOrFail($request->audit_finding_id);
        }

        return view('improvement-opportunities.create', compact('departments', 'users', 'auditFinding'));
    }

    /**
     * Store a newly created Improvement Opportunity in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:internal_audit,external_audit,process_review,management_review,other',
            'audit_finding_id' => 'nullable|exists:audit_responses,id',
            'from_department_id' => 'required|exists:departments,id',
            'to_department_id' => 'required|exists:departments,id',
            'issued_date' => 'required|date',
            'subject' => 'required|string|max:500',
            'observation_description' => 'required|string',
            'improvement_suggestion' => 'nullable|string',
            'clarification' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|in:draft,pending_approval',
        ]);

        // Generate IO number
        $validated['io_number'] = ImprovementOpportunity::generateIoNumber();
        $validated['issued_by'] = Auth::id();

        // Set source_id based on source_type
        if ($validated['source_type'] === 'internal_audit' && $request->filled('audit_finding_id')) {
            $validated['source_id'] = $validated['audit_finding_id'];
        }

        $io = ImprovementOpportunity::create($validated);

        // If created from audit finding, update the finding
        if ($io->audit_finding_id) {
            $auditFinding = AuditResponse::find($io->audit_finding_id);
            if ($auditFinding) {
                $auditFinding->update(['io_created' => true]);
            }
        }

        return redirect()
            ->route('improvement-opportunities.index')
            ->with('success', "Improvement Opportunity {$io->io_number} created successfully.");
    }

    /**
     * Display the specified Improvement Opportunity.
     */
    public function show(ImprovementOpportunity $improvementOpportunity)
    {
        $improvementOpportunity->load([
            'fromDepartment',
            'toDepartment',
            'issuedBy',
            'approvedBy',
            'closedBy',
            'responses.respondedBy',
            'responses.reviewedBy',
            'auditFinding.audit.auditPlan'
        ]);

        return view('improvement-opportunities.show', compact('improvementOpportunity'));
    }

    /**
     * Show the form for editing the specified Improvement Opportunity.
     */
    public function edit(ImprovementOpportunity $improvementOpportunity)
    {
        // Only allow editing if status is draft or rejected_to_be_edited
        if (!in_array($improvementOpportunity->status, ['draft', 'rejected_to_be_edited'])) {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity cannot be edited in its current status.');
        }

        $departments = Department::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('improvement-opportunities.edit', compact('improvementOpportunity', 'departments', 'users'));
    }

    /**
     * Update the specified Improvement Opportunity in storage.
     */
    public function update(Request $request, ImprovementOpportunity $improvementOpportunity)
    {
        // Only allow updating if status is draft or rejected_to_be_edited
        if (!in_array($improvementOpportunity->status, ['draft', 'rejected_to_be_edited'])) {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity cannot be updated in its current status.');
        }

        $validated = $request->validate([
            'source_type' => 'required|in:internal_audit,external_audit,process_review,management_review,other',
            'from_department_id' => 'required|exists:departments,id',
            'to_department_id' => 'required|exists:departments,id',
            'issued_date' => 'required|date',
            'subject' => 'required|string|max:500',
            'observation_description' => 'required|string',
            'improvement_suggestion' => 'nullable|string',
            'clarification' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
        ]);

        $improvementOpportunity->update($validated);

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', "Improvement Opportunity {$improvementOpportunity->io_number} updated successfully.");
    }

    /**
     * Remove the specified Improvement Opportunity from storage.
     */
    public function destroy(ImprovementOpportunity $improvementOpportunity)
    {
        // Only allow deletion if status is draft
        if ($improvementOpportunity->status !== 'draft') {
            return redirect()
                ->route('improvement-opportunities.index')
                ->with('error', 'Only draft Improvement Opportunities can be deleted.');
        }

        $ioNumber = $improvementOpportunity->io_number;
        $improvementOpportunity->delete();

        return redirect()
            ->route('improvement-opportunities.index')
            ->with('success', "Improvement Opportunity {$ioNumber} deleted successfully.");
    }

    /**
     * Submit Improvement Opportunity for approval.
     */
    public function submitForApproval(ImprovementOpportunity $improvementOpportunity)
    {
        if ($improvementOpportunity->status !== 'draft') {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity cannot be submitted in its current status.');
        }

        $improvementOpportunity->update(['status' => 'pending_approval']);

        // Send notification to quality managers
        $qualityManagers = User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Quality Manager', 'Admin', 'Super Admin']);
            })
            ->get();

        foreach ($qualityManagers as $manager) {
            $manager->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'pending_approval'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', "Improvement Opportunity {$improvementOpportunity->io_number} submitted for approval.");
    }

    /**
     * Approve Improvement Opportunity.
     */
    public function approve(Request $request, ImprovementOpportunity $improvementOpportunity)
    {
        if ($improvementOpportunity->status !== 'pending_approval') {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity cannot be approved in its current status.');
        }

        $validated = $request->validate([
            'clarification' => 'nullable|string',
        ]);

        $improvementOpportunity->update([
            'status' => 'issued',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'clarification' => $validated['clarification'] ?? $improvementOpportunity->clarification,
        ]);

        // Send notification to users in the responsible department
        $departmentUsers = User::where('is_active', true)
            ->where('department_id', $improvementOpportunity->to_department_id)
            ->get();

        foreach ($departmentUsers as $user) {
            $user->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'issued'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', "Improvement Opportunity {$improvementOpportunity->io_number} approved and issued to {$improvementOpportunity->toDepartment->name}.");
    }

    /**
     * Reject Improvement Opportunity for editing.
     */
    public function reject(Request $request, ImprovementOpportunity $improvementOpportunity)
    {
        if ($improvementOpportunity->status !== 'pending_approval') {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity cannot be rejected in its current status.');
        }

        $validated = $request->validate([
            'clarification' => 'required|string',
        ]);

        $improvementOpportunity->update([
            'status' => 'rejected_to_be_edited',
            'clarification' => $validated['clarification'],
        ]);

        // Send notification to the issuer
        if ($improvementOpportunity->issuedBy) {
            $improvementOpportunity->issuedBy->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'rejected'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', "Improvement Opportunity {$improvementOpportunity->io_number} rejected for editing.");
    }

    /**
     * Auto-create IOs from observation audit findings.
     */
    public function autoCreateFromObservations()
    {
        // Find all observation findings that don't have IOs yet
        $findings = AuditResponse::where('compliance_status', 'observation')
            ->where('io_created', false)
            ->with(['audit.auditPlan.department', 'audit.auditee'])
            ->get();

        if ($findings->isEmpty()) {
            return redirect()
                ->route('improvement-opportunities.index')
                ->with('info', 'No observation findings without Improvement Opportunities found.');
        }

        $created = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($findings as $finding) {
                // Skip if audit or department data is missing
                if (!$finding->audit || !$finding->audit->auditPlan || !$finding->audit->auditPlan->department) {
                    $errors[] = "Finding #{$finding->id}: Missing audit or department data";
                    continue;
                }

                $io = ImprovementOpportunity::create([
                    'io_number' => ImprovementOpportunity::generateIoNumber(),
                    'source_type' => 'internal_audit',
                    'source_id' => $finding->id,
                    'audit_finding_id' => $finding->id,
                    'from_department_id' => 1, // Quality department - adjust as needed
                    'to_department_id' => $finding->audit->auditPlan->department_id,
                    'issued_date' => now()->toDateString(),
                    'subject' => "Observation: {$finding->question_text}",
                    'observation_description' => $finding->auditor_remarks ?? 'Observation identified during internal audit.',
                    'status' => 'draft',
                    'priority' => 'medium',
                    'issued_by' => Auth::id(),
                ]);

                $finding->update(['io_created' => true]);
                $created++;
            }

            DB::commit();

            $message = "Successfully created {$created} Improvement Opportunit(y/ies) from observation findings.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()
                ->route('improvement-opportunities.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('improvement-opportunities.index')
                ->with('error', 'Failed to create Improvement Opportunities: ' . $e->getMessage());
        }
    }

    /**
     * Close Improvement Opportunity after response is accepted.
     */
    public function close(ImprovementOpportunity $improvementOpportunity)
    {
        // Check if IO can be closed
        if ($improvementOpportunity->status === 'closed') {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This Improvement Opportunity is already closed.');
        }

        // Verify there is an accepted response
        $hasAcceptedResponse = $improvementOpportunity->responses()->where('response_status', 'accepted')->exists();
        if (!$hasAcceptedResponse) {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'Improvement Opportunity cannot be closed without an accepted response.');
        }

        // Close the IO
        $improvementOpportunity->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
        ]);

        // Send notification to department users and the issuer
        $notifyUsers = User::where('is_active', true)
            ->where('department_id', $improvementOpportunity->to_department_id)
            ->get();

        // Also notify the issuer if not already in department users
        if ($improvementOpportunity->issuedBy && !$notifyUsers->contains('id', $improvementOpportunity->issued_by)) {
            $notifyUsers->push($improvementOpportunity->issuedBy);
        }

        foreach ($notifyUsers as $user) {
            $user->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'closed'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', "Improvement Opportunity {$improvementOpportunity->io_number} has been successfully closed.");
    }
}
