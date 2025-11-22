<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExternalAuditController extends Controller
{
    /**
     * Display a listing of external audits
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $type = $request->get('audit_type');

        $query = ExternalAudit::with(['department', 'sector']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('audit_type', $type);
        }

        $audits = $query->orderBy('scheduled_start_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $audits,
        ], 200);
    }

    /**
     * Store a newly created external audit
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audit_number' => 'required|string|unique:external_audits,audit_number',
            'audit_type' => 'required|in:initial,surveillance,recertification,special',
            'standard' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'lead_auditor_name' => 'required|string|max:255',
            'scheduled_start_date' => 'required|date',
            'scheduled_end_date' => 'required|date|after_or_equal:scheduled_start_date',
            'scope' => 'required|string',
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

        $audit = ExternalAudit::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'External audit created successfully',
            'data' => $audit->load(['department', 'sector']),
        ], 201);
    }

    /**
     * Display the specified external audit
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $audit = ExternalAudit::with([
            'department',
            'sector',
            'findings',
            'certificate'
        ])->find($id);

        if (!$audit) {
            return response()->json([
                'success' => false,
                'message' => 'External audit not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $audit,
        ], 200);
    }

    /**
     * Update the specified external audit
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $audit = ExternalAudit::find($id);

        if (!$audit) {
            return response()->json([
                'success' => false,
                'message' => 'External audit not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'audit_number' => 'string|unique:external_audits,audit_number,' . $id,
            'audit_type' => 'in:initial,surveillance,recertification,special',
            'standard' => 'string|max:255',
            'certification_body' => 'string|max:255',
            'lead_auditor_name' => 'string|max:255',
            'scheduled_start_date' => 'date',
            'scheduled_end_date' => 'date|after_or_equal:scheduled_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            'status' => 'in:scheduled,in_progress,completed,cancelled',
            'result' => 'nullable|in:pending,passed,failed,passed_with_minor_nc,passed_with_major_nc',
            'scope' => 'string',
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

        $audit->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'External audit updated successfully',
            'data' => $audit->load(['department', 'sector']),
        ], 200);
    }

    /**
     * Remove the specified external audit
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $audit = ExternalAudit::find($id);

        if (!$audit) {
            return response()->json([
                'success' => false,
                'message' => 'External audit not found',
            ], 404);
        }

        // Only allow deletion of scheduled audits
        if ($audit->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Only scheduled audits can be deleted',
            ], 403);
        }

        $audit->delete();

        return response()->json([
            'success' => true,
            'message' => 'External audit deleted successfully',
        ], 200);
    }

    /**
     * Get statistics for external audits
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => ExternalAudit::count(),
            'scheduled' => ExternalAudit::where('status', 'scheduled')->count(),
            'in_progress' => ExternalAudit::where('status', 'in_progress')->count(),
            'completed' => ExternalAudit::where('status', 'completed')->count(),
            'passed' => ExternalAudit::where('result', 'passed')->count(),
            'with_certificate' => ExternalAudit::whereNotNull('certificate_id')->count(),
            'upcoming' => ExternalAudit::where('status', 'scheduled')
                ->where('scheduled_start_date', '>', now())
                ->where('scheduled_start_date', '<=', now()->addDays(30))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }
}
