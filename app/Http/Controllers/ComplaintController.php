<?php

namespace App\Http\Controllers;

use App\Models\CustomerComplaint;
use App\Models\Department;
use App\Models\User;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    /**
     * Display a listing of customer complaints.
     */
    public function index()
    {
        $complaints = CustomerComplaint::with([
            'assignedToDepartment',
            'assignedToUser',
            'receivedBy',
            'car'
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => CustomerComplaint::count(),
            'new' => CustomerComplaint::where('status', 'new')->count(),
            'investigating' => CustomerComplaint::where('status', 'investigating')->count(),
            'resolved' => CustomerComplaint::where('status', 'resolved')->count(),
            'closed' => CustomerComplaint::where('status', 'closed')->count(),
            'overdue' => CustomerComplaint::whereNotIn('status', ['resolved', 'closed'])
                ->where('response_date', '<', now())
                ->count(),
            'high_priority' => CustomerComplaint::whereIn('priority', ['high', 'critical'])->count(),
            'car_generated' => CustomerComplaint::whereNotNull('car_id')->count(),
        ];

        return view('complaints.index', compact('complaints', 'stats'));
    }

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('complaints.create', compact('departments', 'users'));
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'complaint_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_company' => 'nullable|string|max:255',
            'complaint_subject' => 'required|string|max:255',
            'complaint_description' => 'required|string',
            'complaint_category' => 'required|in:product_quality,service_quality,delivery,documentation,technical_support,billing,other',
            'priority' => 'required|in:low,medium,high,critical',
            'severity' => 'required|in:minor,major,critical',
            'assigned_to_department_id' => 'nullable|exists:departments,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'response_date' => 'nullable|date|after:complaint_date',
            'car_required' => 'boolean',
        ]);

        $validated['complaint_number'] = CustomerComplaint::generateComplaintNumber();
        $validated['received_by'] = Auth::id();
        $validated['status'] = 'new';

        $complaint = CustomerComplaint::create($validated);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', "Complaint {$complaint->complaint_number} created successfully.");
    }

    /**
     * Display the specified complaint.
     */
    public function show(CustomerComplaint $complaint)
    {
        $complaint->load([
            'assignedToDepartment',
            'assignedToUser',
            'receivedBy',
            'resolvedBy',
            'closedBy',
            'car.fromDepartment',
            'car.toDepartment'
        ]);

        return view('complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint.
     */
    public function edit(CustomerComplaint $complaint)
    {
        // Only allow editing of new or acknowledged complaints
        if (!in_array($complaint->status, ['new', 'acknowledged'])) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Only new or acknowledged complaints can be edited.');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('complaints.edit', compact('complaint', 'departments', 'users'));
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, CustomerComplaint $complaint)
    {
        $validated = $request->validate([
            'complaint_date' => 'required|date',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_company' => 'nullable|string|max:255',
            'complaint_subject' => 'required|string|max:255',
            'complaint_description' => 'required|string',
            'complaint_category' => 'required|in:product_quality,service_quality,delivery,documentation,technical_support,billing,other',
            'priority' => 'required|in:low,medium,high,critical',
            'severity' => 'required|in:minor,major,critical',
            'assigned_to_department_id' => 'nullable|exists:departments,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'response_date' => 'nullable|date|after:complaint_date',
            'car_required' => 'boolean',
        ]);

        $complaint->update($validated);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    /**
     * Remove the specified complaint.
     */
    public function destroy(CustomerComplaint $complaint)
    {
        // Only allow deletion of new complaints
        if ($complaint->status !== 'new') {
            return redirect()
                ->route('complaints.index')
                ->with('error', 'Only new complaints can be deleted.');
        }

        $complaintNumber = $complaint->complaint_number;
        $complaint->delete();

        return redirect()
            ->route('complaints.index')
            ->with('success', "Complaint {$complaintNumber} deleted successfully.");
    }

    /**
     * Acknowledge complaint.
     */
    public function acknowledge(Request $request, CustomerComplaint $complaint)
    {
        if ($complaint->status !== 'new') {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Only new complaints can be acknowledged.');
        }

        $validated = $request->validate([
            'initial_response' => 'required|string',
        ]);

        $complaint->update([
            'status' => 'acknowledged',
            'initial_response' => $validated['initial_response'],
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint acknowledged successfully.');
    }

    /**
     * Start investigation.
     */
    public function investigate(CustomerComplaint $complaint)
    {
        if (!in_array($complaint->status, ['acknowledged', 'new'])) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Complaint cannot be moved to investigation in its current status.');
        }

        $complaint->update(['status' => 'investigating']);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint moved to investigation.');
    }

    /**
     * Resolve complaint.
     */
    public function resolve(Request $request, CustomerComplaint $complaint)
    {
        if (!in_array($complaint->status, ['investigating', 'acknowledged'])) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Complaint must be in investigation or acknowledged status to be resolved.');
        }

        $validated = $request->validate([
            'root_cause_analysis' => 'required|string',
            'corrective_action' => 'required|string',
            'resolution' => 'required|string',
        ]);

        $validated['status'] = 'resolved';
        $validated['resolved_date'] = now()->toDateString();
        $validated['resolved_by'] = Auth::id();

        $complaint->update($validated);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint resolved successfully.');
    }

    /**
     * Close complaint.
     */
    public function close(CustomerComplaint $complaint)
    {
        if (!$complaint->canBeClosed()) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Complaint cannot be closed. Ensure it is resolved and any required CAR is created.');
        }

        $complaint->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
        ]);

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('success', 'Complaint closed successfully.');
    }

    /**
     * Generate CAR from complaint.
     */
    public function generateCar(CustomerComplaint $complaint)
    {
        if (!$complaint->canGenerateCar()) {
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'CAR cannot be generated for this complaint.');
        }

        DB::beginTransaction();
        try {
            $car = Car::create([
                'car_number' => Car::generateCarNumber(),
                'source_type' => 'customer_complaint',
                'source_id' => $complaint->id,
                'from_department_id' => 1, // Quality department - adjust as needed
                'to_department_id' => $complaint->assigned_to_department_id ?? 1,
                'issued_date' => now()->toDateString(),
                'subject' => "Customer Complaint: {$complaint->complaint_subject}",
                'ncr_description' => $complaint->complaint_description,
                'status' => 'draft',
                'priority' => $complaint->priority,
                'issued_by' => Auth::id(),
            ]);

            $complaint->update(['car_id' => $car->id]);

            DB::commit();

            return redirect()
                ->route('cars.show', $car)
                ->with('success', "CAR {$car->car_number} generated successfully from complaint {$complaint->complaint_number}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('complaints.show', $complaint)
                ->with('error', 'Failed to generate CAR: ' . $e->getMessage());
        }
    }
}
