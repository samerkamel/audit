<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CertificateController extends Controller
{
    /**
     * Display a listing of certificates
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $type = $request->get('certificate_type');

        $query = Certificate::with(['issuedForAudit', 'createdBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('certificate_type', $type);
        }

        $certificates = $query->orderBy('expiry_date', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ], 200);
    }

    /**
     * Store a newly created certificate
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificate_number' => 'required|string|unique:certificates,certificate_number',
            'standard' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'certificate_type' => 'required|in:initial,renewal,transfer',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'scope_of_certification' => 'required|string',
            'covered_sites' => 'nullable|array',
            'covered_processes' => 'nullable|array',
            'notes' => 'nullable|string',
            'issued_for_audit_id' => 'nullable|exists:external_audits,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['status'] = 'valid';

        $certificate = Certificate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Certificate created successfully',
            'data' => $certificate->load(['issuedForAudit', 'createdBy']),
        ], 201);
    }

    /**
     * Display the specified certificate
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $certificate = Certificate::with([
            'issuedForAudit',
            'createdBy'
        ])->find($id);

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        // Add days until expiry
        $certificate->days_until_expiry = now()->diffInDays($certificate->expiry_date, false);

        return response()->json([
            'success' => true,
            'data' => $certificate,
        ], 200);
    }

    /**
     * Update the specified certificate
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'certificate_number' => 'string|unique:certificates,certificate_number,' . $id,
            'standard' => 'string|max:255',
            'certification_body' => 'string|max:255',
            'certificate_type' => 'in:initial,renewal,transfer',
            'issue_date' => 'date',
            'expiry_date' => 'date|after:issue_date',
            'status' => 'in:valid,expiring_soon,expired,suspended,revoked',
            'scope_of_certification' => 'string',
            'covered_sites' => 'nullable|array',
            'covered_processes' => 'nullable|array',
            'notes' => 'nullable|string',
            'issued_for_audit_id' => 'nullable|exists:external_audits,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $certificate->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Certificate updated successfully',
            'data' => $certificate->load(['issuedForAudit', 'createdBy']),
        ], 200);
    }

    /**
     * Remove the specified certificate
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        // Only allow deletion of expired or revoked certificates
        if (!in_array($certificate->status, ['expired', 'revoked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only expired or revoked certificates can be deleted',
            ], 403);
        }

        $certificate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certificate deleted successfully',
        ], 200);
    }

    /**
     * Get statistics for certificates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Certificate::count(),
            'valid' => Certificate::where('status', 'valid')->count(),
            'expiring_soon' => Certificate::where('status', 'expiring_soon')->count(),
            'expired' => Certificate::where('status', 'expired')->count(),
            'suspended' => Certificate::where('status', 'suspended')->count(),
            'revoked' => Certificate::where('status', 'revoked')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }

    /**
     * Get expiring certificates
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function expiring(Request $request)
    {
        $days = (int) $request->get('days', 30);

        $certificates = Certificate::with(['issuedForAudit', 'createdBy'])
            ->whereIn('status', ['valid', 'expiring_soon'])
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ], 200);
    }
}
