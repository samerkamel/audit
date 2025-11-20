<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    /**
     * Display a listing of sectors.
     */
    public function index(Request $request)
    {
        $query = Sector::with(['director', 'departments', 'users']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by director
        if ($request->filled('director_id')) {
            $query->where('director_id', $request->director_id);
        }

        $sectors = $query->latest()->paginate(15);
        $directors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'sector_director', 'manager']);
        })->get();

        // Calculate statistics
        $stats = [
            'total' => Sector::count(),
            'active' => Sector::where('is_active', true)->count(),
            'inactive' => Sector::where('is_active', false)->count(),
            'with_director' => Sector::whereNotNull('director_id')->count(),
        ];

        return view('sectors.index', compact('sectors', 'directors', 'stats'));
    }

    /**
     * Show the form for creating a new sector.
     */
    public function create()
    {
        $directors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'sector_director', 'manager']);
        })->get();

        return view('sectors.create', compact('directors'));
    }

    /**
     * Store a newly created sector.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sectors'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sectors'],
            'director_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $sector = Sector::create($validated);

        return redirect()->route('sectors.index')
            ->with('success', 'Sector created successfully.');
    }

    /**
     * Display the specified sector.
     */
    public function show(Sector $sector)
    {
        $sector->load(['director', 'departments.users', 'users']);

        // Calculate sector statistics
        $stats = [
            'total_departments' => $sector->departments()->count(),
            'active_departments' => $sector->departments()->where('is_active', true)->count(),
            'total_users' => $sector->users()->count(),
            'active_users' => $sector->users()->where('is_active', true)->count(),
        ];

        return view('sectors.show', compact('sector', 'stats'));
    }

    /**
     * Show the form for editing the specified sector.
     */
    public function edit(Sector $sector)
    {
        $directors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'sector_director', 'manager']);
        })->get();

        return view('sectors.edit', compact('sector', 'directors'));
    }

    /**
     * Update the specified sector.
     */
    public function update(Request $request, Sector $sector)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sectors,name,' . $sector->id],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sectors,code,' . $sector->id],
            'director_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $sector->update($validated);

        return redirect()->route('sectors.index')
            ->with('success', 'Sector updated successfully.');
    }

    /**
     * Soft delete the specified sector.
     */
    public function destroy(Sector $sector)
    {
        // Check if sector has departments or users
        if ($sector->departments()->count() > 0) {
            return redirect()->route('sectors.index')
                ->with('error', 'Cannot delete sector with existing departments. Please reassign or delete departments first.');
        }

        if ($sector->users()->count() > 0) {
            return redirect()->route('sectors.index')
                ->with('error', 'Cannot delete sector with existing users. Please reassign users first.');
        }

        $sector->delete();

        return redirect()->route('sectors.index')
            ->with('success', 'Sector deleted successfully.');
    }

    /**
     * Reactivate a deactivated sector.
     */
    public function reactivate(Sector $sector)
    {
        $sector->update(['is_active' => true]);

        return redirect()->route('sectors.index')
            ->with('success', 'Sector reactivated successfully.');
    }
}
