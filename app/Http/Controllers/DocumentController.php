<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with(['owner', 'reviewer', 'approver'])
            ->orderBy('document_number', 'desc')
            ->get();

        $stats = [
            'total' => Document::count(),
            'effective' => Document::where('status', 'effective')->count(),
            'pending_review' => Document::where('status', 'pending_review')->count(),
            'pending_approval' => Document::where('status', 'pending_approval')->count(),
            'needs_review' => Document::needingReview()->count(),
            'obsolete' => Document::where('status', 'obsolete')->count(),
        ];

        return view('documents.index', compact('documents', 'stats'));
    }

    public function create()
    {
        $departments = Department::all();
        $users = User::all();
        $effectiveDocuments = Document::effective()->get();
        
        return view('documents.create', compact('departments', 'users', 'effectiveDocuments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:quality_manual,procedure,work_instruction,form,record,external_document',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'applicable_departments' => 'nullable|array',
            'keywords' => 'nullable|string',
            'supersedes_id' => 'nullable|exists:documents,id',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        $documentNumber = Document::generateDocumentNumber($validated['category']);

        $data = [
            'document_number' => $documentNumber,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $validated['owner_id'],
            'applicable_departments' => $validated['applicable_departments'] ?? [],
            'keywords' => $validated['keywords'] ? explode(',', $validated['keywords']) : [],
            'supersedes_id' => $validated['supersedes_id'] ?? null,
            'created_by' => Auth::id(),
            'status' => 'draft',
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $document = Document::create($data);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
    }

    public function show(Document $document)
    {
        $document->load(['owner', 'reviewer', 'approver', 'createdBy', 'updatedBy', 'supersedes', 'supersededBy']);
        
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        if (!$document->canBeEdited()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'This document cannot be edited in its current status.');
        }

        $departments = Department::all();
        $users = User::all();
        $effectiveDocuments = Document::effective()->where('id', '!=', $document->id)->get();
        
        return view('documents.edit', compact('document', 'departments', 'users', 'effectiveDocuments'));
    }

    public function update(Request $request, Document $document)
    {
        if (!$document->canBeEdited()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'This document cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
            'applicable_departments' => 'nullable|array',
            'keywords' => 'nullable|string',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $validated['owner_id'],
            'applicable_departments' => $validated['applicable_departments'] ?? [],
            'keywords' => $validated['keywords'] ? explode(',', $validated['keywords']) : [],
            'updated_by' => Auth::id(),
        ];

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $document->update($data);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        if ($document->isEffective()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Effective documents cannot be deleted. Please make them obsolete first.');
        }

        // Delete file if exists
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    // Workflow methods
    public function submitForReview(Document $document)
    {
        if (!$document->submitForReview()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Document cannot be submitted for review.');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document submitted for review.');
    }

    public function review(Document $document)
    {
        if (!$document->review(Auth::id())) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Document cannot be reviewed.');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document reviewed and sent for approval.');
    }

    public function approve(Document $document)
    {
        if (!$document->approve(Auth::id())) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Document cannot be approved.');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document approved successfully.');
    }

    public function makeEffective(Document $document)
    {
        if (!$document->makeEffective()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Document cannot be made effective.');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document is now effective.');
    }

    public function makeObsolete(Document $document)
    {
        if (!$document->makeObsolete()) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'Document cannot be made obsolete.');
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document marked as obsolete.');
    }

    public function download(Document $document)
    {
        if (!$document->file_path) {
            return redirect()->route('documents.show', $document)
                ->with('error', 'No file available for download.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
