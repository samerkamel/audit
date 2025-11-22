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
        $category = $request->get('category');

        $query = Document::with(['owner', 'reviewer', 'approver']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
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
            'category' => 'required|in:quality_manual,procedure,work_instruction,form,record,external_document',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'effective_date' => 'nullable|date',
            'next_review_date' => 'nullable|date|after:effective_date',
            'owner_id' => 'required|exists:users,id',
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

        $document = Document::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Document created successfully',
            'data' => $document->load(['owner']),
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
            'owner',
            'reviewer',
            'approver',
            'createdBy',
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
            'category' => 'in:quality_manual,procedure,work_instruction,form,record,external_document',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:20',
            'status' => 'in:draft,pending_review,pending_approval,approved,effective,obsolete,archived',
            'effective_date' => 'nullable|date',
            'next_review_date' => 'nullable|date|after:effective_date',
            'owner_id' => 'exists:users,id',
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
            'data' => $document->load(['owner', 'reviewer', 'approver']),
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
            'effective' => Document::where('status', 'effective')->count(),
            'pending_review' => Document::where('status', 'pending_review')->count(),
            'pending_approval' => Document::where('status', 'pending_approval')->count(),
            'obsolete' => Document::where('status', 'obsolete')->count(),
            'due_for_review' => Document::where('next_review_date', '<=', now())
                ->where('status', 'effective')
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
        $documents = Document::with(['owner'])
            ->where('next_review_date', '<=', now())
            ->where('status', 'effective')
            ->orderBy('next_review_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ], 200);
    }
}
