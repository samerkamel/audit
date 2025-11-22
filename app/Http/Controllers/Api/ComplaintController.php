<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
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

        $query = Complaint::with(['department', 'sector', 'assignedTo', 'resolvedBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($severity) {
            $query->where('severity', $severity);
        }

        if ($category) {
            $query->where('category', $category);
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
            'complaint_number' => 'required|string|unique:complaints,complaint_number',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'category' => 'required|in:product_quality,service_quality,delivery,documentation,other',
            'severity' => 'required|in:low,medium,high,critical',
            'complaint_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'sector_id' => 'nullable|exists:sectors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint = Complaint::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Complaint created successfully',
            'data' => $complaint->load(['department', 'sector', 'assignedTo']),
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
        $complaint = Complaint::with([
            'department',
            'sector',
            'assignedTo',
            'resolvedBy',
            'correctiveActions'
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
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'complaint_number' => 'string|unique:complaints,complaint_number,' . $id,
            'subject' => 'string|max:255',
            'description' => 'string',
            'customer_name' => 'string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'category' => 'in:product_quality,service_quality,delivery,documentation,other',
            'severity' => 'in:low,medium,high,critical',
            'status' => 'in:new,investigating,action_required,resolved,closed,rejected',
            'complaint_date' => 'date',
            'resolution_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'resolved_by' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'sector_id' => 'nullable|exists:sectors,id',
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
            'data' => $complaint->load(['department', 'sector', 'assignedTo', 'resolvedBy']),
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
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'Complaint not found',
            ], 404);
        }

        // Only allow deletion of new or rejected complaints
        if (!in_array($complaint->status, ['new', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only new or rejected complaints can be deleted',
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
            'total' => Complaint::count(),
            'new' => Complaint::where('status', 'new')->count(),
            'investigating' => Complaint::where('status', 'investigating')->count(),
            'action_required' => Complaint::where('status', 'action_required')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'closed' => Complaint::where('status', 'closed')->count(),
            'critical' => Complaint::where('severity', 'critical')->count(),
            'high' => Complaint::where('severity', 'high')->count(),
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
        $complaints = Complaint::with(['department', 'sector', 'assignedTo'])
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->orderBy('severity', 'desc')
            ->orderBy('complaint_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ], 200);
    }
}
