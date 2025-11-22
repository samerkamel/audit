<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\AuditResponse;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    /**
     * Display a listing of CARs.
     */
    public function index(Request $request)
    {
        $query = Car::with([
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
                $q->where('car_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('ncr_description', 'like', "%{$search}%");
            });
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $cars = $query->paginate(15);

        // Statistics for dashboard cards
        $statistics = [
            'total' => Car::count(),
            'draft' => Car::where('status', 'draft')->count(),
            'pending_approval' => Car::where('status', 'pending_approval')->count(),
            'issued' => Car::where('status', 'issued')->count(),
            'in_progress' => Car::where('status', 'in_progress')->count(),
            'pending_review' => Car::where('status', 'pending_review')->count(),
            'closed' => Car::where('status', 'closed')->count(),
            'late' => Car::where('status', 'late')->count(),
            'critical_priority' => Car::where('priority', 'critical')->count(),
            'overdue' => Car::overdue()->count(),
        ];

        $departments = Department::orderBy('name')->get();

        return view('cars.index', compact('cars', 'statistics', 'departments'));
    }

    /**
     * Show the form for creating a new CAR.
     */
    public function create(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // If coming from an audit finding, pre-populate
        $auditFinding = null;
        if ($request->filled('audit_finding_id')) {
            $auditFinding = AuditResponse::with([
                'audit.auditPlan.department',
                'audit.auditee'
            ])->findOrFail($request->audit_finding_id);
        }

        return view('cars.create', compact('departments', 'users', 'auditFinding'));
    }

    /**
     * Store a newly created CAR in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_type' => 'required|in:internal_audit,external_audit,customer_complaint,process_performance,other',
            'audit_finding_id' => 'nullable|exists:audit_responses,id',
            'from_department_id' => 'required|exists:departments,id',
            'to_department_id' => 'required|exists:departments,id',
            'issued_date' => 'required|date',
            'subject' => 'required|string|max:500',
            'ncr_description' => 'required|string',
            'clarification' => 'nullable|string',
            'priority' => 'required|in:critical,high,medium,low',
            'status' => 'required|in:draft,pending_approval',
        ]);

        // Generate CAR number
        $validated['car_number'] = Car::generateCarNumber();
        $validated['issued_by'] = Auth::id();

        // Set source_id based on source_type
        if ($validated['source_type'] === 'internal_audit' && $request->filled('audit_finding_id')) {
            $validated['source_id'] = $validated['audit_finding_id'];
        }

        $car = Car::create($validated);

        // If created from audit finding, update the finding
        if ($car->audit_finding_id) {
            $auditFinding = AuditResponse::find($car->audit_finding_id);
            if ($auditFinding) {
                $auditFinding->update(['car_created' => true]);
            }
        }

        return redirect()
            ->route('cars.index')
            ->with('success', "CAR {$car->car_number} created successfully.");
    }

    /**
     * Display the specified CAR.
     */
    public function show(Car $car)
    {
        $car->load([
            'fromDepartment',
            'toDepartment',
            'issuedBy',
            'approvedBy',
            'responses.respondedBy',
            'responses.reviewedBy',
            'followUps.followedUpBy',
            'auditFinding.audit.auditPlan'
        ]);

        return view('cars.show', compact('car'));
    }

    /**
     * Show the form for editing the specified CAR.
     */
    public function edit(Car $car)
    {
        // Only allow editing if status is draft or rejected_to_be_edited
        if (!in_array($car->status, ['draft', 'rejected_to_be_edited'])) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR cannot be edited in its current status.');
        }

        $departments = Department::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('cars.edit', compact('car', 'departments', 'users'));
    }

    /**
     * Update the specified CAR in storage.
     */
    public function update(Request $request, Car $car)
    {
        // Only allow updating if status is draft or rejected_to_be_edited
        if (!in_array($car->status, ['draft', 'rejected_to_be_edited'])) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR cannot be updated in its current status.');
        }

        $validated = $request->validate([
            'source_type' => 'required|in:internal_audit,external_audit,customer_complaint,process_performance,other',
            'from_department_id' => 'required|exists:departments,id',
            'to_department_id' => 'required|exists:departments,id',
            'issued_date' => 'required|date',
            'subject' => 'required|string|max:500',
            'ncr_description' => 'required|string',
            'clarification' => 'nullable|string',
            'priority' => 'required|in:critical,high,medium,low',
        ]);

        $car->update($validated);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "CAR {$car->car_number} updated successfully.");
    }

    /**
     * Remove the specified CAR from storage.
     */
    public function destroy(Car $car)
    {
        // Only allow deletion if status is draft
        if ($car->status !== 'draft') {
            return redirect()
                ->route('cars.index')
                ->with('error', 'Only draft CARs can be deleted.');
        }

        $carNumber = $car->car_number;
        $car->delete();

        return redirect()
            ->route('cars.index')
            ->with('success', "CAR {$carNumber} deleted successfully.");
    }

    /**
     * Submit CAR for approval.
     */
    public function submitForApproval(Car $car)
    {
        if ($car->status !== 'draft') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR cannot be submitted in its current status.');
        }

        $car->update(['status' => 'pending_approval']);

        // TODO: Send notification to quality manager

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "CAR {$car->car_number} submitted for approval.");
    }

    /**
     * Approve CAR.
     */
    public function approve(Request $request, Car $car)
    {
        if ($car->status !== 'pending_approval') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR cannot be approved in its current status.');
        }

        $validated = $request->validate([
            'clarification' => 'nullable|string',
        ]);

        $car->update([
            'status' => 'issued',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'clarification' => $validated['clarification'] ?? $car->clarification,
        ]);

        // TODO: Send notification to responsible department

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "CAR {$car->car_number} approved and issued to {$car->toDepartment->name}.");
    }

    /**
     * Reject CAR for editing.
     */
    public function reject(Request $request, Car $car)
    {
        if ($car->status !== 'pending_approval') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR cannot be rejected in its current status.');
        }

        $validated = $request->validate([
            'clarification' => 'required|string',
        ]);

        $car->update([
            'status' => 'rejected_to_be_edited',
            'clarification' => $validated['clarification'],
        ]);

        // TODO: Send notification to issuer

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "CAR {$car->car_number} rejected for editing.");
    }

    /**
     * Auto-create CARs from non-compliant audit findings.
     */
    public function autoCreateFromFindings()
    {
        // Find all non-compliant findings that don't have CARs yet
        $findings = AuditResponse::where('compliance_status', 'non_compliant')
            ->where('car_created', false)
            ->with(['audit.auditPlan.department', 'audit.auditee'])
            ->get();

        if ($findings->isEmpty()) {
            return redirect()
                ->route('cars.index')
                ->with('info', 'No non-compliant findings without CARs found.');
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

                $car = Car::create([
                    'car_number' => Car::generateCarNumber(),
                    'source_type' => 'internal_audit',
                    'source_id' => $finding->id,
                    'audit_finding_id' => $finding->id,
                    'from_department_id' => 1, // Quality department - adjust as needed
                    'to_department_id' => $finding->audit->auditPlan->department_id,
                    'issued_date' => now()->toDateString(),
                    'subject' => "Non-Compliance: {$finding->question_text}",
                    'ncr_description' => $finding->auditor_remarks ?? 'Non-compliance identified during internal audit.',
                    'status' => 'draft',
                    'priority' => 'high',
                    'issued_by' => Auth::id(),
                ]);

                $finding->update(['car_created' => true]);
                $created++;
            }

            DB::commit();

            $message = "Successfully created {$created} CAR(s) from non-compliant findings.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()
                ->route('cars.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('cars.index')
                ->with('error', 'Failed to create CARs: ' . $e->getMessage());
        }
    }

    /**
     * Close CAR after all follow-ups are accepted.
     */
    public function close(Car $car)
    {
        // Check if CAR can be closed
        if ($car->status === 'closed') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This CAR is already closed.');
        }

        // Verify all responses are accepted
        $hasAcceptedResponse = $car->responses()->where('response_status', 'accepted')->exists();
        if (!$hasAcceptedResponse) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'CAR cannot be closed without an accepted response.');
        }

        // Verify all follow-ups are accepted
        $hasUnacceptedFollowUps = $car->followUps()
            ->where('status', '!=', 'accepted')
            ->exists();

        if ($hasUnacceptedFollowUps) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'CAR cannot be closed while there are pending or not-accepted follow-ups.');
        }

        // Verify at least one follow-up exists
        if ($car->followUps()->count() === 0) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'CAR requires at least one effectiveness review before closure.');
        }

        // Close the CAR
        $car->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
        ]);

        // TODO: Send notification to department and stakeholders

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "CAR {$car->car_number} has been successfully closed.");
    }
}
