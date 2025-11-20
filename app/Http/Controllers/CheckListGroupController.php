<?php

namespace App\Http\Controllers;

use App\Models\CheckListGroup;
use App\Models\AuditQuestion;
use Illuminate\Http\Request;

class CheckListGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CheckListGroup::query()->with('auditQuestions');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('quality_procedure_reference', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $checklistGroups = $query->orderBy('display_order')->orderBy('code')->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => CheckListGroup::count(),
            'active' => CheckListGroup::where('is_active', true)->count(),
            'with_questions' => CheckListGroup::has('auditQuestions')->count(),
            'departments' => CheckListGroup::distinct('department')->whereNotNull('department')->count('department'),
        ];

        // Get unique departments for filter
        $departments = CheckListGroup::distinct('department')
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        return view('checklist-groups.index', compact('checklistGroups', 'stats', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $auditQuestions = AuditQuestion::active()->orderBy('display_order')->orderBy('code')->get();
        return view('checklist-groups.create', compact('auditQuestions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:check_list_groups,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quality_procedure_reference' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['exists:audit_questions,id'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $checklistGroup = CheckListGroup::create($validated);

        // Attach questions if any
        if (!empty($validated['questions'])) {
            $syncData = [];
            foreach ($validated['questions'] as $index => $questionId) {
                $syncData[$questionId] = ['display_order' => $index];
            }
            $checklistGroup->auditQuestions()->sync($syncData);
        }

        return redirect()->route('checklist-groups.index')
            ->with('success', 'Checklist group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CheckListGroup $checklistGroup)
    {
        $checklistGroup->load('auditQuestions');
        return view('checklist-groups.show', compact('checklistGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CheckListGroup $checklistGroup)
    {
        $checklistGroup->load('auditQuestions');
        $auditQuestions = AuditQuestion::active()->orderBy('display_order')->orderBy('code')->get();
        $selectedQuestions = $checklistGroup->auditQuestions->pluck('id')->toArray();

        return view('checklist-groups.edit', compact('checklistGroup', 'auditQuestions', 'selectedQuestions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CheckListGroup $checklistGroup)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:check_list_groups,code,' . $checklistGroup->id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quality_procedure_reference' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['exists:audit_questions,id'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        $checklistGroup->update($validated);

        // Update questions
        if (isset($validated['questions'])) {
            $syncData = [];
            foreach ($validated['questions'] as $index => $questionId) {
                $syncData[$questionId] = ['display_order' => $index];
            }
            $checklistGroup->auditQuestions()->sync($syncData);
        } else {
            $checklistGroup->auditQuestions()->sync([]);
        }

        return redirect()->route('checklist-groups.index')
            ->with('success', 'Checklist group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CheckListGroup $checklistGroup)
    {
        $checklistGroup->delete();

        return redirect()->route('checklist-groups.index')
            ->with('success', 'Checklist group deleted successfully.');
    }

    /**
     * Reactivate a soft-deleted checklist group.
     */
    public function reactivate($id)
    {
        $checklistGroup = CheckListGroup::withTrashed()->findOrFail($id);
        $checklistGroup->restore();

        return redirect()->route('checklist-groups.index')
            ->with('success', 'Checklist group reactivated successfully.');
    }
}
