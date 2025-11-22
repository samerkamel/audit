<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of CARs
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $priority = $request->get('priority');

        $query = Car::with(['department', 'sector', 'assignedTo', 'verifiedBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        $cars = $query->orderBy('issued_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $cars,
        ], 200);
    }

    /**
     * Store a newly created CAR
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_number' => 'required|string|unique:cars,car_number',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'source' => 'required|in:internal_audit,external_audit,customer_complaint,management_review,process_monitoring,other',
            'priority' => 'required|in:low,medium,high,critical',
            'issued_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issued_date',
            'assigned_to' => 'required|exists:users,id',
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

        $car = Car::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'CAR created successfully',
            'data' => $car->load(['department', 'sector', 'assignedTo']),
        ], 201);
    }

    /**
     * Display the specified CAR
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $car = Car::with([
            'department',
            'sector',
            'assignedTo',
            'verifiedBy',
            'rootCauseAnalysis',
            'correctiveActions'
        ])->find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'CAR not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $car,
        ], 200);
    }

    /**
     * Update the specified CAR
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'CAR not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'car_number' => 'string|unique:cars,car_number,' . $id,
            'subject' => 'string|max:255',
            'description' => 'string',
            'source' => 'in:internal_audit,external_audit,customer_complaint,management_review,process_monitoring,other',
            'priority' => 'in:low,medium,high,critical',
            'status' => 'in:open,in_progress,pending_verification,closed,cancelled',
            'issued_date' => 'date',
            'due_date' => 'date|after_or_equal:issued_date',
            'completion_date' => 'nullable|date',
            'assigned_to' => 'exists:users,id',
            'verified_by' => 'nullable|exists:users,id',
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

        $car->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'CAR updated successfully',
            'data' => $car->load(['department', 'sector', 'assignedTo', 'verifiedBy']),
        ], 200);
    }

    /**
     * Remove the specified CAR
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'CAR not found',
            ], 404);
        }

        // Only allow deletion of open CARs
        if (!in_array($car->status, ['open', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only open or cancelled CARs can be deleted',
            ], 403);
        }

        $car->delete();

        return response()->json([
            'success' => true,
            'message' => 'CAR deleted successfully',
        ], 200);
    }

    /**
     * Get statistics for CARs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Car::count(),
            'open' => Car::where('status', 'open')->count(),
            'in_progress' => Car::where('status', 'in_progress')->count(),
            'pending_verification' => Car::where('status', 'pending_verification')->count(),
            'closed' => Car::where('status', 'closed')->count(),
            'overdue' => Car::where('due_date', '<', now())
                ->whereNotIn('status', ['closed', 'cancelled'])
                ->count(),
            'late' => Car::where('completion_date', '>', \DB::raw('due_date'))
                ->where('status', 'closed')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }
}
