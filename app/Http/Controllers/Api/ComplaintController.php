<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * Display a listing of complaints
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $severity = $request->get('severity');
        $category = $request->get('category');

        $query = CustomerComplaint::with(['assignedToDepartment', 'assignedToUser', 'receivedBy', 'resolvedBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($category) {
            $query->where('complaint_category', $category);
        }

        $complaints = $query->orderBy('complaint_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ], 200);
    }

    /**
     * Store a newly created complaint
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complaint_number' => 'required|string|unique:customer_complaints,complaint_number',
            'complaint_subject' => 'required|string|max:255',
            'complaint_description' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'complaint_category' => 'required|in:product_quality,service_quality,delivery,documentation,technical_support,billing,other',
            'severity' => 'required|in:minor,major,critical',
            'priority' => 'nullable|in:low,medium,high,critical',
            'complaint_date' => 'required|date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_to_department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['received_by'] = auth()->id();

        $complaint = CustomerComplaint::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Complaint created successfully',
            'data' => $complaint->load(['assignedToDepartment', 'assignedToUser', 'receivedBy']),
        ], 201);
    }

    /**
     * Display the specified complaint
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $complaint = CustomerComplaint::with([
            'assignedToDepartment',
            'assignedToUser',
            'receivedBy',
            'resolvedBy',
            'car'
        ])->find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $complaint,
        ], 200);
    }

    /**
     * Update the specified complaint
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $complaint = CustomerComplaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'complaint_number' => 'string|unique:customer_complaints,complaint_number,' . $id,
            'complaint_subject' => 'string|max:255',
            'complaint_description' => 'string',
            'customer_name' => 'string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'complaint_category' => 'in:product_quality,service_quality,delivery,documentation,technical_support,billing,other',
            'severity' => 'in:minor,major,critical',
            'priority' => 'in:low,medium,high,critical',
            'status' => 'in:new,acknowledged,investigating,resolved,closed,escalated',
            'complaint_date' => 'date',
            'resolved_date' => 'nullable|date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'resolved_by' => 'nullable|exists:users,id',
            'assigned_to_department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Complaint updated successfully',
            'data' => $complaint->load(['assignedToDepartment', 'assignedToUser', 'receivedBy', 'resolvedBy']),
        ], 200);
    }

    /**
     * Remove the specified complaint
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $complaint = CustomerComplaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        // Only allow deletion of new complaints
        if ($complaint->status !== 'new') {
            return response()->json([
                'success' => false,
                'message' => 'Only new complaints can be deleted',
            ], 403);
        }

        $complaint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Complaint deleted successfully',
        ], 200);
    }

    /**
     * Get statistics for complaints
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => CustomerComplaint::count(),
            'new' => CustomerComplaint::where('status', 'new')->count(),
            'acknowledged' => CustomerComplaint::where('status', 'acknowledged')->count(),
            'investigating' => CustomerComplaint::where('status', 'investigating')->count(),
            'resolved' => CustomerComplaint::where('status', 'resolved')->count(),
            'closed' => CustomerComplaint::where('status', 'closed')->count(),
            'escalated' => CustomerComplaint::where('status', 'escalated')->count(),
            'critical_severity' => CustomerComplaint::where('severity', 'critical')->count(),
            'major_severity' => CustomerComplaint::where('severity', 'major')->count(),
            'critical_priority' => CustomerComplaint::where('priority', 'critical')->count(),
            'high_priority' => CustomerComplaint::where('priority', 'high')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }

    /**
     * Get unresolved complaints
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unresolved()
    {
        $complaints = CustomerComplaint::with(['assignedToDepartment', 'assignedToUser', 'receivedBy'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->orderBy('priority', 'desc')
            ->orderBy('complaint_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ], 200);
    }
}
