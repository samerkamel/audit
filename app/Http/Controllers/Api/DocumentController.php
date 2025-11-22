<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $categoryId = $request->get('category_id');

        $query = Document::with(['category', 'department', 'sector', 'owner', 'approvedBy']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $documents = $query->orderBy('document_number', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $documents,
        ], 200);
    }

    /**
     * Store a newly created document
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_number' => 'required|string|unique:documents,document_number',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:document_categories,id',
            'description' => 'nullable|string',
            'revision' => 'nullable|string|max:50',
            'issue_date' => 'required|date',
            'review_date' => 'nullable|date|after:issue_date',
            'owner_id' => 'required|exists:users,id',
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

        $document = Document::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Document created successfully',
            'data' => $document->load(['category', 'department', 'sector', 'owner']),
        ], 201);
    }

    /**
     * Display the specified document
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $document = Document::with([
            'category',
            'department',
            'sector',
            'owner',
            'approvedBy',
            'revisions'
        ])->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document,
        ], 200);
    }

    /**
     * Update the specified document
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'document_number' => 'string|unique:documents,document_number,' . $id,
            'title' => 'string|max:255',
            'category_id' => 'exists:document_categories,id',
            'description' => 'nullable|string',
            'revision' => 'nullable|string|max:50',
            'status' => 'in:draft,active,under_review,obsolete,archived',
            'issue_date' => 'date',
            'review_date' => 'nullable|date|after:issue_date',
            'approval_date' => 'nullable|date',
            'owner_id' => 'exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
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

        $document->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => $document->load(['category', 'department', 'sector', 'owner', 'approvedBy']),
        ], 200);
    }

    /**
     * Remove the specified document
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        // Only allow deletion of draft or obsolete documents
        if (!in_array($document->status, ['draft', 'obsolete'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or obsolete documents can be deleted',
            ], 403);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ], 200);
    }

    /**
     * Get statistics for documents
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Document::count(),
            'draft' => Document::where('status', 'draft')->count(),
            'active' => Document::where('status', 'active')->count(),
            'under_review' => Document::where('status', 'under_review')->count(),
            'obsolete' => Document::where('status', 'obsolete')->count(),
            'due_for_review' => Document::where('review_date', '<=', now())
                ->where('status', 'active')
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ], 200);
    }

    /**
     * Get documents due for review
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dueForReview()
    {
        $documents = Document::with(['category', 'department', 'sector', 'owner'])
            ->where('review_date', '<=', now())
            ->where('status', 'active')
            ->orderBy('review_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ], 200);
    }
}
