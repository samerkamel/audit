<?php

namespace App\Http\Controllers;

use App\Models\AuditQuestion;
use Illuminate\Http\Request;

class AuditQuestionController extends Controller
{
    /**
     * Display a listing of audit questions.
     */
    public function index(Request $request)
    {
        $query = AuditQuestion::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('question', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $auditQuestions = $query->orderBy('display_order')->orderBy('code')->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => AuditQuestion::count(),
            'active' => AuditQuestion::where('is_active', true)->count(),
            'required' => AuditQuestion::where('is_required', true)->count(),
            'by_category' => AuditQuestion::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
        ];

        return view('audit-questions.index', compact('auditQuestions', 'stats'));
    }

    /**
     * Show the form for creating a new audit question.
     */
    public function create()
    {
        return view('audit-questions.create');
    }

    /**
     * Store a newly created audit question.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:audit_questions,code'],
            'question' => ['required', 'string'],
            'category' => ['required', 'in:compliance,operational,financial,it,quality,security'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_required'] = $validated['is_required'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        AuditQuestion::create($validated);

        return redirect()->route('audit-questions.index')
            ->with('success', 'Audit question created successfully.');
    }

    /**
     * Display the specified audit question.
     */
    public function show(AuditQuestion $auditQuestion)
    {
        return view('audit-questions.show', compact('auditQuestion'));
    }

    /**
     * Show the form for editing the specified audit question.
     */
    public function edit(AuditQuestion $auditQuestion)
    {
        return view('audit-questions.edit', compact('auditQuestion'));
    }

    /**
     * Update the specified audit question.
     */
    public function update(Request $request, AuditQuestion $auditQuestion)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:audit_questions,code,' . $auditQuestion->id],
            'question' => ['required', 'string'],
            'category' => ['required', 'in:compliance,operational,financial,it,quality,security'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_required'] = $validated['is_required'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? false;

        $auditQuestion->update($validated);

        return redirect()->route('audit-questions.index')
            ->with('success', 'Audit question updated successfully.');
    }

    /**
     * Soft delete the specified audit question.
     */
    public function destroy(AuditQuestion $auditQuestion)
    {
        $auditQuestion->delete();

        return redirect()->route('audit-questions.index')
            ->with('success', 'Audit question deleted successfully.');
    }

    /**
     * Reactivate a soft-deleted audit question.
     */
    public function reactivate($id)
    {
        $auditQuestion = AuditQuestion::withTrashed()->findOrFail($id);
        $auditQuestion->restore();

        return redirect()->route('audit-questions.index')
            ->with('success', 'Audit question reactivated successfully.');
    }
}
