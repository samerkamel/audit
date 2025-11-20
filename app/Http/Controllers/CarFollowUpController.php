<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CarFollowUpController extends Controller
{
    /**
     * Show the form for creating a new follow-up.
     */
    public function create(Car $car)
    {
        // Only allow follow-up creation if CAR has accepted response
        if (!$car->responses()->where('response_status', 'accepted')->exists()) {
            return redirect()->route('cars.show', $car)
                ->with('error', 'Follow-ups can only be created for CARs with accepted responses.');
        }

        // Check if CAR is already closed
        if ($car->status === 'closed') {
            return redirect()->route('cars.show', $car)
                ->with('error', 'Cannot add follow-ups to closed CARs.');
        }

        return view('cars.follow-ups.create', compact('car'));
    }

    /**
     * Store a newly created follow-up.
     */
    public function store(Request $request, Car $car)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'effectiveness_review' => 'required|string',
            'verification_evidence' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
            'remarks' => 'nullable|string',
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('car-follow-up-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['car_id'] = $car->id;
        $validated['follow_up_by'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['attachments'] = $attachments;
        $validated['reviewed_at'] = now();

        CarFollowUp::create($validated);

        // Update CAR status to pending_review if not already
        if ($car->status === 'in_progress') {
            $car->update(['status' => 'pending_review']);
        }

        return redirect()->route('cars.show', $car)
            ->with('success', 'Follow-up created successfully.');
    }

    /**
     * Show the form for editing a follow-up.
     */
    public function edit(Car $car, CarFollowUp $followUp)
    {
        // Only allow editing of pending or not_accepted follow-ups
        if (!in_array($followUp->status, ['pending', 'not_accepted'])) {
            return redirect()->route('cars.show', $car)
                ->with('error', 'Only pending or not accepted follow-ups can be edited.');
        }

        return view('cars.follow-ups.edit', compact('car', 'followUp'));
    }

    /**
     * Update the specified follow-up.
     */
    public function update(Request $request, Car $car, CarFollowUp $followUp)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'effectiveness_review' => 'required|string',
            'verification_evidence' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
            'remarks' => 'nullable|string',
        ]);

        // Handle new file uploads
        $existingAttachments = $followUp->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('car-follow-up-attachments', 'public');
                $existingAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        $validated['attachments'] = $existingAttachments;
        $validated['reviewed_at'] = now();
        $validated['status'] = 'pending';

        $followUp->update($validated);

        return redirect()->route('cars.show', $car)
            ->with('success', 'Follow-up updated successfully.');
    }

    /**
     * Mark follow-up as accepted (effective).
     */
    public function accept(Request $request, Car $car, CarFollowUp $followUp)
    {
        if ($followUp->status === 'accepted') {
            return redirect()->route('cars.show', $car)
                ->with('error', 'This follow-up is already accepted.');
        }

        $followUp->update([
            'status' => 'accepted',
            'reviewed_at' => now(),
        ]);

        // Check if all follow-ups are accepted
        $allFollowUpsAccepted = $car->followUps()
            ->where('status', '!=', 'accepted')
            ->doesntExist();

        // If all follow-ups are accepted, suggest closure
        if ($allFollowUpsAccepted && $car->status !== 'closed') {
            return redirect()->route('cars.show', $car)
                ->with('success', 'Follow-up accepted. All follow-ups are effective. You can now close this CAR.');
        }

        return redirect()->route('cars.show', $car)
            ->with('success', 'Follow-up accepted as effective.');
    }

    /**
     * Mark follow-up as not accepted (not effective).
     */
    public function reject(Request $request, Car $car, CarFollowUp $followUp)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $followUp->update([
            'status' => 'not_accepted',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at' => now(),
        ]);

        return redirect()->route('cars.show', $car)
            ->with('success', 'Follow-up marked as not effective. Additional actions required.');
    }

    /**
     * Remove attachment from follow-up.
     */
    public function removeAttachment(Request $request, Car $car, CarFollowUp $followUp)
    {
        $validated = $request->validate([
            'attachment_index' => 'required|integer',
        ]);

        $attachments = $followUp->attachments ?? [];
        $index = $validated['attachment_index'];

        if (!isset($attachments[$index])) {
            return redirect()->back()->with('error', 'Attachment not found.');
        }

        // Delete file from storage
        if (isset($attachments[$index]['path'])) {
            Storage::disk('public')->delete($attachments[$index]['path']);
        }

        // Remove from array
        unset($attachments[$index]);
        $attachments = array_values($attachments); // Re-index array

        $followUp->update(['attachments' => $attachments]);

        return redirect()->back()->with('success', 'Attachment removed successfully.');
    }

    /**
     * Delete a follow-up.
     */
    public function destroy(Car $car, CarFollowUp $followUp)
    {
        // Only allow deletion of pending follow-ups
        if ($followUp->status !== 'pending') {
            return redirect()->route('cars.show', $car)
                ->with('error', 'Only pending follow-ups can be deleted.');
        }

        // Delete attachments from storage
        if ($followUp->attachments) {
            foreach ($followUp->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $followUp->delete();

        return redirect()->route('cars.show', $car)
            ->with('success', 'Follow-up deleted successfully.');
    }
}
