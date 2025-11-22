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

        $query = Certificate::with(['department', 'sector', 'audit']);

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
            'certificate_name' => 'required|string|max:255',
            'certificate_type' => 'required|in:iso_certification,accreditation,license,other',
            'issuing_authority' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'scope' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'audit_id' => 'nullable|exists:external_audits,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $certificate = Certificate::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Certificate created successfully',
            'data' => $certificate->load(['department', 'sector', 'audit']),
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
            'department',
            'sector',
            'audit'
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
            'certificate_name' => 'string|max:255',
            'certificate_type' => 'in:iso_certification,accreditation,license,other',
            'issuing_authority' => 'string|max:255',
            'issue_date' => 'date',
            'expiry_date' => 'date|after:issue_date',
            'renewal_date' => 'nullable|date',
            'status' => 'in:active,expiring_soon,expired,suspended,revoked',
            'scope' => 'string',
            'department_id' => 'nullable|exists:departments,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'audit_id' => 'nullable|exists:external_audits,id',
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
            'data' => $certificate->load(['department', 'sector', 'audit']),
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
            'active' => Certificate::where('status', 'active')->count(),
            'expiring_soon' => Certificate::where('status', 'expiring_soon')->count(),
            'expired' => Certificate::where('status', 'expired')->count(),
            'expiring_30_days' => Certificate::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->count(),
            'expiring_90_days' => Certificate::where('expiry_date', '<=', now()->addDays(90))
                ->where('expiry_date', '>', now())
                ->count(),
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
        $days = $request->get('days', 30);

        $certificates = Certificate::with(['department', 'sector'])
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
