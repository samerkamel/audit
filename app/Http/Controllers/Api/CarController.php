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

        $query = Car::with(['fromDepartment', 'toDepartment', 'issuedBy']);

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
            'ncr_description' => 'required|string',
            'source_type' => 'required|in:internal_audit,external_audit,customer_complaint,process_performance,other',
            'priority' => 'required|in:low,medium,high,critical',
            'issued_date' => 'required|date',
            'from_department_id' => 'nullable|exists:departments,id',
            'to_department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['issued_by'] = auth()->id();
        $data['status'] = 'draft';

        $car = Car::create($data);

        return response()->json([
            'success' => true,
            'message' => 'CAR created successfully',
            'data' => $car->load(['fromDepartment', 'toDepartment', 'issuedBy']),
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
            'fromDepartment',
            'toDepartment',
            'issuedBy',
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
            'ncr_description' => 'string',
            'source_type' => 'in:internal_audit,external_audit,customer_complaint,process_performance,other',
            'priority' => 'in:low,medium,high,critical',
            'status' => 'in:draft,pending_approval,issued,in_progress,pending_review,rejected_to_be_edited,closed,late',
            'issued_date' => 'date',
            'from_department_id' => 'nullable|exists:departments,id',
            'to_department_id' => 'nullable|exists:departments,id',
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
            'data' => $car->load(['fromDepartment', 'toDepartment', 'issuedBy']),
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

        // Only allow deletion of draft CARs
        if ($car->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft CARs can be deleted',
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
            'draft' => Car::where('status', 'draft')->count(),
            'pending_approval' => Car::where('status', 'pending_approval')->count(),
            'issued' => Car::where('status', 'issued')->count(),
            'in_progress' => Car::where('status', 'in_progress')->count(),
            'pending_review' => Car::where('status', 'pending_review')->count(),
            'closed' => Car::where('status', 'closed')->count(),
            'late' => Car::where('status', 'late')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }
}
