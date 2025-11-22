<?php

namespace App\Http\Controllers;

use App\Models\ImprovementOpportunity;
use App\Models\ImprovementOpportunityResponse;
use App\Notifications\ImprovementOpportunityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImprovementOpportunityResponseController extends Controller
{
    /**
     * Show the form for creating a new response.
     */
    public function create(ImprovementOpportunity $improvementOpportunity)
    {
        // Check if IO is in a state that allows responses
        if (!in_array($improvementOpportunity->status, ['issued', 'in_progress', 'pending_review'])) {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'Responses cannot be added in the current status.');
        }

        return view('improvement-opportunities.responses.create', compact('improvementOpportunity'));
    }

    /**
     * Store a newly created response.
     */
    public function store(Request $request, ImprovementOpportunity $improvementOpportunity)
    {
        $validated = $request->validate([
            'proposed_action' => 'required|string',
            'implementation_plan' => 'nullable|string',
            'target_date' => 'required|date|after:today',
            'outcome' => 'nullable|string',
        ]);

        $validated['improvement_opportunity_id'] = $improvementOpportunity->id;
        $validated['responded_by'] = Auth::id();
        $validated['responded_at'] = now();
        $validated['response_status'] = 'submitted';

        $response = ImprovementOpportunityResponse::create($validated);

        // Update IO status to in_progress if it was issued
        if ($improvementOpportunity->status === 'issued') {
            $improvementOpportunity->update(['status' => 'in_progress']);
        }

        // Notify quality team
        $qualityManagers = \App\Models\User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Quality Manager', 'Admin', 'Super Admin']);
            })
            ->get();

        foreach ($qualityManagers as $manager) {
            $manager->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'response_submitted'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', 'Response submitted successfully.');
    }

    /**
     * Show the form for editing the specified response.
     */
    public function edit(ImprovementOpportunity $improvementOpportunity, ImprovementOpportunityResponse $response)
    {
        // Only allow editing if response is rejected or pending
        if (!in_array($response->response_status, ['pending', 'rejected'])) {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'This response cannot be edited.');
        }

        return view('improvement-opportunities.responses.edit', compact('improvementOpportunity', 'response'));
    }

    /**
     * Update the specified response.
     */
    public function update(Request $request, ImprovementOpportunity $improvementOpportunity, ImprovementOpportunityResponse $response)
    {
        $validated = $request->validate([
            'proposed_action' => 'required|string',
            'implementation_plan' => 'nullable|string',
            'target_date' => 'required|date',
            'actual_date' => 'nullable|date',
            'outcome' => 'nullable|string',
        ]);

        $validated['response_status'] = 'submitted';
        $validated['responded_at'] = now();

        $response->update($validated);

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', 'Response updated successfully.');
    }

    /**
     * Accept the response.
     */
    public function accept(Request $request, ImprovementOpportunity $improvementOpportunity, ImprovementOpportunityResponse $response)
    {
        if ($response->response_status !== 'submitted') {
            return redirect()
                ->route('improvement-opportunities.show', $improvementOpportunity)
                ->with('error', 'Only submitted responses can be accepted.');
        }

        $response->update([
            'response_status' => 'accepted',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Update IO status
        $improvementOpportunity->update(['status' => 'pending_review']);

        // Notify the responder
        if ($response->respondedBy) {
            $response->respondedBy->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'response_accepted'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', 'Response accepted successfully.');
    }

    /**
     * Reject the response.
     */
    public function reject(Request $request, ImprovementOpportunity $improvementOpportunity, ImprovementOpportunityResponse $response)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $response->update([
            'response_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Notify the responder
        if ($response->respondedBy) {
            $response->respondedBy->notify(new ImprovementOpportunityNotification($improvementOpportunity, 'response_rejected'));
        }

        return redirect()
            ->route('improvement-opportunities.show', $improvementOpportunity)
            ->with('success', 'Response rejected.');
    }
}
