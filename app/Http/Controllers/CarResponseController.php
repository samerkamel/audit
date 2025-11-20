<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CarResponseController extends Controller
{
    /**
     * Show the form for creating a new response.
     */
    public function create(Car $car)
    {
        // Only allow response creation if CAR is issued or in_progress
        if (!in_array($car->status, ['issued', 'in_progress'])) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Responses can only be added to issued CARs.');
        }

        // Check if user's department matches the target department
        $user = Auth::user();
        if ($user->department_id !== $car->to_department_id) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only the assigned department can respond to this CAR.');
        }

        $car->load(['fromDepartment', 'toDepartment', 'issuedBy']);

        return view('cars.responses.create', compact('car'));
    }

    /**
     * Store a newly created response in storage.
     */
    public function store(Request $request, Car $car)
    {
        // Validate department access
        $user = Auth::user();
        if ($user->department_id !== $car->to_department_id) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only the assigned department can respond to this CAR.');
        }

        $validated = $request->validate([
            'root_cause' => 'required|string',
            'correction' => 'required|string',
            'correction_target_date' => 'required|date|after:today',
            'corrective_action' => 'required|string',
            'corrective_action_target_date' => 'required|date|after:correction_target_date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('car-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['attachments'] = $attachments;
        $validated['car_id'] = $car->id;
        $validated['responded_by'] = Auth::id();
        $validated['responded_at'] = now();
        $validated['response_status'] = 'submitted';

        $response = CarResponse::create($validated);

        // Update CAR status to in_progress
        if ($car->status === 'issued') {
            $car->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Response submitted successfully and is pending quality team review.");
    }

    /**
     * Show the form for editing the specified response.
     */
    public function edit(Car $car, CarResponse $response)
    {
        // Only allow editing if response is pending or rejected
        if (!in_array($response->response_status, ['pending', 'rejected'])) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This response cannot be edited in its current status.');
        }

        // Check if user's department matches
        $user = Auth::user();
        if ($user->department_id !== $car->to_department_id) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only the assigned department can edit this response.');
        }

        $car->load(['fromDepartment', 'toDepartment', 'issuedBy']);

        return view('cars.responses.edit', compact('car', 'response'));
    }

    /**
     * Update the specified response in storage.
     */
    public function update(Request $request, Car $car, CarResponse $response)
    {
        // Validate department access
        $user = Auth::user();
        if ($user->department_id !== $car->to_department_id) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only the assigned department can update this response.');
        }

        // Only allow updating if status is pending or rejected
        if (!in_array($response->response_status, ['pending', 'rejected'])) {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'This response cannot be updated in its current status.');
        }

        $validated = $request->validate([
            'root_cause' => 'required|string',
            'correction' => 'required|string',
            'correction_target_date' => 'required|date|after:today',
            'corrective_action' => 'required|string',
            'corrective_action_target_date' => 'required|date|after:correction_target_date',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        // Handle new file uploads
        $existingAttachments = $response->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('car-attachments', 'public');
                $existingAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['attachments'] = $existingAttachments;
        $validated['response_status'] = 'submitted';
        $validated['responded_at'] = now();

        $response->update($validated);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Response updated and re-submitted for review.");
    }

    /**
     * Accept a CAR response (Quality Team).
     */
    public function accept(Request $request, Car $car, CarResponse $response)
    {
        if ($response->response_status !== 'submitted') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only submitted responses can be accepted.');
        }

        $response->update([
            'response_status' => 'accepted',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Update CAR status to pending_review (waiting for follow-up)
        $car->update(['status' => 'pending_review']);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Response accepted. CAR is now pending effectiveness review.");
    }

    /**
     * Reject a CAR response (Quality Team).
     */
    public function reject(Request $request, Car $car, CarResponse $response)
    {
        if ($response->response_status !== 'submitted') {
            return redirect()
                ->route('cars.show', $car)
                ->with('error', 'Only submitted responses can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $response->update([
            'response_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Response rejected. Department has been notified to revise.");
    }

    /**
     * Update correction actual date.
     */
    public function updateCorrectionDate(Request $request, Car $car, CarResponse $response)
    {
        $validated = $request->validate([
            'correction_actual_date' => 'required|date',
        ]);

        $response->update($validated);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Correction completion date updated.");
    }

    /**
     * Update corrective action actual date.
     */
    public function updateCorrectiveActionDate(Request $request, Car $car, CarResponse $response)
    {
        $validated = $request->validate([
            'corrective_action_actual_date' => 'required|date',
        ]);

        $response->update($validated);

        return redirect()
            ->route('cars.show', $car)
            ->with('success', "Corrective action completion date updated.");
    }

    /**
     * Remove an attachment from the response.
     */
    public function removeAttachment(Request $request, Car $car, CarResponse $response)
    {
        $validated = $request->validate([
            'attachment_index' => 'required|integer',
        ]);

        $attachments = $response->attachments ?? [];
        $index = $validated['attachment_index'];

        if (isset($attachments[$index])) {
            // Delete file from storage
            if (isset($attachments[$index]['path'])) {
                Storage::disk('public')->delete($attachments[$index]['path']);
            }

            // Remove from array
            array_splice($attachments, $index, 1);

            $response->update(['attachments' => $attachments]);

            return redirect()
                ->route('cars.show', $car)
                ->with('success', "Attachment removed successfully.");
        }

        return redirect()
            ->route('cars.show', $car)
            ->with('error', "Attachment not found.");
    }
}
