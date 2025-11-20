<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $query = Department::with(['sector', 'manager', 'users']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by sector
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by manager
        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->manager_id);
        }

        $departments = $query->latest()->paginate(15);
        $sectors = Sector::where('is_active', true)->get();
        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'manager', 'department_manager']);
        })->get();

        // Calculate statistics
        $stats = [
            'total' => Department::count(),
            'active' => Department::where('is_active', true)->count(),
            'inactive' => Department::where('is_active', false)->count(),
            'with_manager' => Department::whereNotNull('manager_id')->count(),
        ];

        return view('departments.index', compact('departments', 'sectors', 'managers', 'stats'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        $sectors = Sector::where('is_active', true)->get();
        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'manager', 'department_manager']);
        })->get();

        return view('departments.create', compact('sectors', 'managers'));
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sector_id' => ['required', 'exists:sectors,id'],
            'name' => ['required', 'string', 'max:255', 'unique:departments'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:departments'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $department = Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load(['sector', 'manager', 'users']);

        // Calculate department statistics
        $stats = [
            'total_users' => $department->users()->count(),
            'active_users' => $department->users()->where('is_active', true)->count(),
            'inactive_users' => $department->users()->where('is_active', false)->count(),
        ];

        return view('departments.show', compact('department', 'stats'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        $sectors = Sector::where('is_active', true)->get();
        $managers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'manager', 'department_manager']);
        })->get();

        return view('departments.edit', compact('department', 'sectors', 'managers'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'sector_id' => ['required', 'exists:sectors,id'],
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:departments,code,' . $department->id],
            'manager_id' => ['nullable', 'exists:users,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Soft delete the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has users
        if ($department->users()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete department with existing users. Please reassign users first.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Reactivate a deactivated department.
     */
    public function reactivate(Department $department)
    {
        $department->update(['is_active' => true]);

        return redirect()->route('departments.index')
            ->with('success', 'Department reactivated successfully.');
    }
}
