<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use Illuminate\Http\Request;

class ReminderSettingController extends Controller
{
    /**
     * Display a listing of reminder settings.
     */
    public function index()
    {
        $settings = ReminderSetting::orderBy('entity_type')
            ->orderBy('event_type')
            ->get()
            ->groupBy('entity_type');

        $entityTypes = ReminderSetting::getEntityTypes();
        $eventTypes = ReminderSetting::getEventTypes();
        $intervalOptions = ReminderSetting::getIntervalOptions();

        return view('reminder-settings.index', compact('settings', 'entityTypes', 'eventTypes', 'intervalOptions'));
    }

    /**
     * Show the form for creating a new reminder setting.
     */
    public function create()
    {
        $entityTypes = ReminderSetting::getEntityTypes();
        $eventTypes = ReminderSetting::getEventTypes();
        $intervalOptions = ReminderSetting::getIntervalOptions();

        return view('reminder-settings.create', compact('entityTypes', 'eventTypes', 'intervalOptions'));
    }

    /**
     * Store a newly created reminder setting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string|in:' . implode(',', array_keys(ReminderSetting::getEntityTypes())),
            'event_type' => 'required|string|in:' . implode(',', array_keys(ReminderSetting::getEventTypes())),
            'intervals' => 'required|array|min:1',
            'intervals.*' => 'integer|in:' . implode(',', array_keys(ReminderSetting::getIntervalOptions())),
            'send_email' => 'boolean',
            'send_database' => 'boolean',
            'notification_template_code' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Check for existing setting with same entity_type and event_type
        $existing = ReminderSetting::where('entity_type', $validated['entity_type'])
            ->where('event_type', $validated['event_type'])
            ->first();

        if ($existing) {
            return back()->withErrors(['entity_type' => 'A reminder setting for this entity type and event type already exists.'])->withInput();
        }

        $setting = ReminderSetting::create([
            'name' => $validated['name'],
            'entity_type' => $validated['entity_type'],
            'event_type' => $validated['event_type'],
            'intervals' => $validated['intervals'],
            'send_email' => $request->boolean('send_email'),
            'send_database' => $request->boolean('send_database', true),
            'notification_template_code' => $validated['notification_template_code'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('reminder-settings.index')
            ->with('success', 'Reminder setting created successfully.');
    }

    /**
     * Show the form for editing a reminder setting.
     */
    public function edit(ReminderSetting $reminderSetting)
    {
        $entityTypes = ReminderSetting::getEntityTypes();
        $eventTypes = ReminderSetting::getEventTypes();
        $intervalOptions = ReminderSetting::getIntervalOptions();

        return view('reminder-settings.edit', compact('reminderSetting', 'entityTypes', 'eventTypes', 'intervalOptions'));
    }

    /**
     * Update the specified reminder setting.
     */
    public function update(Request $request, ReminderSetting $reminderSetting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'intervals' => 'required|array|min:1',
            'intervals.*' => 'integer|in:' . implode(',', array_keys(ReminderSetting::getIntervalOptions())),
            'send_email' => 'boolean',
            'send_database' => 'boolean',
            'notification_template_code' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $reminderSetting->update([
            'name' => $validated['name'],
            'intervals' => $validated['intervals'],
            'send_email' => $request->boolean('send_email'),
            'send_database' => $request->boolean('send_database', true),
            'notification_template_code' => $validated['notification_template_code'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('reminder-settings.index')
            ->with('success', 'Reminder setting updated successfully.');
    }

    /**
     * Toggle the active status of a reminder setting.
     */
    public function toggleStatus(ReminderSetting $reminderSetting)
    {
        $reminderSetting->update([
            'is_active' => !$reminderSetting->is_active,
        ]);

        $status = $reminderSetting->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Reminder setting {$status} successfully.");
    }

    /**
     * Remove the specified reminder setting.
     */
    public function destroy(ReminderSetting $reminderSetting)
    {
        $reminderSetting->delete();

        return redirect()->route('reminder-settings.index')
            ->with('success', 'Reminder setting deleted successfully.');
    }
}
