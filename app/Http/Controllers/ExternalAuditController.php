<?php

namespace App\Http\Controllers;

use App\Models\ExternalAudit;
use App\Models\User;
use App\Models\Department;
use App\Notifications\ExternalAuditNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalAuditController extends Controller
{
    /**
     * Display a listing of external audits.
     */
    public function index()
    {
        $audits = ExternalAudit::with(['createdBy', 'coordinator', 'certificate'])
            ->orderBy('scheduled_start_date', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => ExternalAudit::count(),
            'scheduled' => ExternalAudit::where('status', 'scheduled')->count(),
            'in_progress' => ExternalAudit::where('status', 'in_progress')->count(),
            'completed' => ExternalAudit::where('status', 'completed')->count(),
            'upcoming' => ExternalAudit::where('status', 'scheduled')
                ->where('scheduled_start_date', '>', now())
                ->where('scheduled_start_date', '<=', now()->addDays(30))
                ->count(),
            'passed' => ExternalAudit::where('result', 'passed')->count(),
            'with_certificate' => ExternalAudit::has('certificate')->count(),
        ];

        return view('external-audits.index', compact('audits', 'stats'));
    }

    /**
     * Show the form for creating a new external audit.
     */
    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('external-audits.create', compact('users', 'departments'));
    }

    /**
     * Store a newly created external audit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_type' => 'required|in:initial_certification,surveillance,recertification,special,follow_up',
            'certification_body' => 'required|string|max:255',
            'standard' => 'required|string|max:255',
            'lead_auditor_name' => 'required|string|max:255',
            'lead_auditor_email' => 'nullable|email|max:255',
            'lead_auditor_phone' => 'nullable|string|max:50',
            'scheduled_start_date' => 'required|date',
            'scheduled_end_date' => 'required|date|after_or_equal:scheduled_start_date',
            'coordinator_id' => 'nullable|exists:users,id',
            'audited_departments' => 'nullable|array',
            'audited_processes' => 'nullable|array',
            'scope_description' => 'nullable|string',
        ]);

        $validated['audit_number'] = ExternalAudit::generateAuditNumber();
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'scheduled';
        $validated['result'] = 'pending';

        $audit = ExternalAudit::create($validated);

        // Notify quality managers about scheduled audit
        $this->notifyQualityTeam($audit, 'scheduled');

        // Also notify the coordinator if assigned
        if ($audit->coordinator) {
            $audit->coordinator->notify(new ExternalAuditNotification($audit, 'scheduled'));
        }

        return redirect()
            ->route('external-audits.show', $audit)
            ->with('success', "External audit {$audit->audit_number} scheduled successfully.");
    }

    /**
     * Display the specified external audit.
     */
    public function show(ExternalAudit $externalAudit)
    {
        $externalAudit->load(['createdBy', 'coordinator', 'certificate']);

        return view('external-audits.show', compact('externalAudit'));
    }

    /**
     * Show the form for editing the specified external audit.
     */
    public function edit(ExternalAudit $externalAudit)
    {
        // Only allow editing of scheduled or in-progress audits
        if (!in_array($externalAudit->status, ['scheduled', 'in_progress'])) {
            return redirect()
                ->route('external-audits.show', $externalAudit)
                ->with('error', 'Only scheduled or in-progress audits can be edited.');
        }

        $users = User::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('external-audits.edit', compact('externalAudit', 'users', 'departments'));
    }

    /**
     * Update the specified external audit.
     */
    public function update(Request $request, ExternalAudit $externalAudit)
    {
        $validated = $request->validate([
            'audit_type' => 'required|in:initial_certification,surveillance,recertification,special,follow_up',
            'certification_body' => 'required|string|max:255',
            'standard' => 'required|string|max:255',
            'lead_auditor_name' => 'required|string|max:255',
            'lead_auditor_email' => 'nullable|email|max:255',
            'lead_auditor_phone' => 'nullable|string|max:50',
            'scheduled_start_date' => 'required|date',
            'scheduled_end_date' => 'required|date|after_or_equal:scheduled_start_date',
            'coordinator_id' => 'nullable|exists:users,id',
            'audited_departments' => 'nullable|array',
            'audited_processes' => 'nullable|array',
            'scope_description' => 'nullable|string',
        ]);

        $externalAudit->update($validated);

        return redirect()
            ->route('external-audits.show', $externalAudit)
            ->with('success', 'External audit updated successfully.');
    }

    /**
     * Remove the specified external audit.
     */
    public function destroy(ExternalAudit $externalAudit)
    {
        // Only allow deletion of scheduled audits
        if ($externalAudit->status !== 'scheduled') {
            return redirect()
                ->route('external-audits.index')
                ->with('error', 'Only scheduled audits can be deleted.');
        }

        $auditNumber = $externalAudit->audit_number;
        $externalAudit->delete();

        return redirect()
            ->route('external-audits.index')
            ->with('success', "External audit {$auditNumber} deleted successfully.");
    }

    /**
     * Start the external audit.
     */
    public function start(ExternalAudit $externalAudit)
    {
        if (!$externalAudit->canStart()) {
            return redirect()
                ->route('external-audits.show', $externalAudit)
                ->with('error', 'This audit cannot be started.');
        }

        $externalAudit->update([
            'status' => 'in_progress',
            'actual_start_date' => now()->toDateString(),
        ]);

        // Notify quality team and coordinator about audit start
        $this->notifyQualityTeam($externalAudit, 'started');

        if ($externalAudit->coordinator) {
            $externalAudit->coordinator->notify(new ExternalAuditNotification($externalAudit, 'started'));
        }

        return redirect()
            ->route('external-audits.show', $externalAudit)
            ->with('success', 'Audit started successfully.');
    }

    /**
     * Complete the external audit.
     */
    public function complete(Request $request, ExternalAudit $externalAudit)
    {
        if (!$externalAudit->canComplete()) {
            return redirect()
                ->route('external-audits.show', $externalAudit)
                ->with('error', 'This audit cannot be completed.');
        }

        $validated = $request->validate([
            'actual_end_date' => 'required|date|after_or_equal:actual_start_date',
            'result' => 'required|in:passed,conditional,failed',
            'major_ncrs_count' => 'required|integer|min:0',
            'minor_ncrs_count' => 'required|integer|min:0',
            'observations_count' => 'required|integer|min:0',
            'opportunities_count' => 'required|integer|min:0',
            'audit_summary' => 'required|string',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'next_audit_date' => 'nullable|date|after:actual_end_date',
        ]);

        $validated['status'] = 'completed';

        $externalAudit->update($validated);

        // Notify quality team about completion
        $this->notifyQualityTeam($externalAudit, 'completed');

        return redirect()
            ->route('external-audits.show', $externalAudit)
            ->with('success', 'Audit completed successfully.');
    }

    /**
     * Cancel the external audit.
     */
    public function cancel(ExternalAudit $externalAudit)
    {
        if ($externalAudit->status === 'completed') {
            return redirect()
                ->route('external-audits.show', $externalAudit)
                ->with('error', 'Completed audits cannot be cancelled.');
        }

        $externalAudit->update(['status' => 'cancelled']);

        // Notify coordinator about cancellation
        if ($externalAudit->coordinator) {
            $externalAudit->coordinator->notify(new ExternalAuditNotification($externalAudit, 'cancelled'));
        }

        return redirect()
            ->route('external-audits.show', $externalAudit)
            ->with('success', 'Audit cancelled successfully.');
    }

    /**
     * Notify quality team about external audit events.
     */
    protected function notifyQualityTeam(ExternalAudit $audit, string $action): void
    {
        $qualityManagers = User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Quality Manager', 'Admin', 'Super Admin']);
            })
            ->get();

        foreach ($qualityManagers as $manager) {
            $manager->notify(new ExternalAuditNotification($audit, $action));
        }
    }
}
