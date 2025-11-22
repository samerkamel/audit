<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ExternalAudit;
use App\Models\User;
use App\Notifications\CertificateStatusChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Display a listing of certificates.
     */
    public function index()
    {
        $certificates = Certificate::with(['issuedForAudit', 'createdBy'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => Certificate::count(),
            'valid' => Certificate::where('status', 'valid')->count(),
            'expiring_soon' => Certificate::where('status', 'expiring_soon')->count(),
            'expired' => Certificate::where('status', 'expired')->count(),
            'suspended' => Certificate::where('status', 'suspended')->count(),
            'revoked' => Certificate::where('status', 'revoked')->count(),
        ];

        return view('certificates.index', compact('certificates', 'stats'));
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create(Request $request)
    {
        // If coming from an external audit, pre-fill the audit
        $audit = null;
        if ($request->has('audit')) {
            $audit = ExternalAudit::findOrFail($request->audit);
        }

        $audits = ExternalAudit::where('status', 'completed')
            ->whereIn('result', ['passed', 'conditional'])
            ->whereDoesntHave('certificate')
            ->orderBy('actual_end_date', 'desc')
            ->get();

        return view('certificates.create', compact('audits', 'audit'));
    }

    /**
     * Store a newly created certificate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_number' => 'required|string|max:255|unique:certificates,certificate_number',
            'standard' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'certificate_type' => 'required|in:initial,renewal,transfer',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'scope_of_certification' => 'required|string',
            'covered_sites' => 'nullable|array',
            'covered_processes' => 'nullable|array',
            'notes' => 'nullable|string',
            'issued_for_audit_id' => 'nullable|exists:external_audits,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'valid';

        $certificate = Certificate::create($validated);

        // Update certificate status based on expiry date
        $certificate->updateStatus();

        // Notify quality managers about new certificate
        $this->notifyQualityTeam($certificate, 'created');

        return redirect()
            ->route('certificates.show', $certificate)
            ->with('success', "Certificate {$certificate->certificate_number} created successfully.");
    }

    /**
     * Display the specified certificate.
     */
    public function show(Certificate $certificate)
    {
        $certificate->load(['issuedForAudit', 'createdBy']);

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified certificate.
     */
    public function edit(Certificate $certificate)
    {
        // Only allow editing of valid or expiring_soon certificates
        if (!in_array($certificate->status, ['valid', 'expiring_soon'])) {
            return redirect()
                ->route('certificates.show', $certificate)
                ->with('error', 'Only valid or expiring certificates can be edited.');
        }

        $audits = ExternalAudit::where('status', 'completed')
            ->whereIn('result', ['passed', 'conditional'])
            ->orderBy('actual_end_date', 'desc')
            ->get();

        return view('certificates.edit', compact('certificate', 'audits'));
    }

    /**
     * Update the specified certificate.
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'certificate_number' => 'required|string|max:255|unique:certificates,certificate_number,' . $certificate->id,
            'standard' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'certificate_type' => 'required|in:initial,renewal,transfer',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'status' => 'required|in:valid,expiring_soon,expired,suspended,revoked',
            'scope_of_certification' => 'required|string',
            'covered_sites' => 'nullable|array',
            'covered_processes' => 'nullable|array',
            'notes' => 'nullable|string',
            'issued_for_audit_id' => 'nullable|exists:external_audits,id',
        ]);

        $certificate->update($validated);

        // Update certificate status based on expiry date
        $certificate->updateStatus();

        return redirect()
            ->route('certificates.show', $certificate)
            ->with('success', 'Certificate updated successfully.');
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(Certificate $certificate)
    {
        // Only allow deletion of expired or revoked certificates
        if (!in_array($certificate->status, ['expired', 'revoked'])) {
            return redirect()
                ->route('certificates.index')
                ->with('error', 'Only expired or revoked certificates can be deleted.');
        }

        $certificateNumber = $certificate->certificate_number;
        $certificate->delete();

        return redirect()
            ->route('certificates.index')
            ->with('success', "Certificate {$certificateNumber} deleted successfully.");
    }

    /**
     * Suspend the certificate.
     */
    public function suspend(Certificate $certificate)
    {
        if ($certificate->status === 'suspended') {
            return redirect()
                ->route('certificates.show', $certificate)
                ->with('error', 'Certificate is already suspended.');
        }

        $certificate->update(['status' => 'suspended']);

        // Notify quality managers about suspension
        $this->notifyQualityTeam($certificate, 'suspended');

        return redirect()
            ->route('certificates.show', $certificate)
            ->with('success', 'Certificate suspended successfully.');
    }

    /**
     * Revoke the certificate.
     */
    public function revoke(Certificate $certificate)
    {
        if ($certificate->status === 'revoked') {
            return redirect()
                ->route('certificates.show', $certificate)
                ->with('error', 'Certificate is already revoked.');
        }

        $certificate->update(['status' => 'revoked']);

        // Notify quality managers about revocation
        $this->notifyQualityTeam($certificate, 'revoked');

        return redirect()
            ->route('certificates.show', $certificate)
            ->with('success', 'Certificate revoked successfully.');
    }

    /**
     * Reinstate a suspended certificate.
     */
    public function reinstate(Certificate $certificate)
    {
        if ($certificate->status !== 'suspended') {
            return redirect()
                ->route('certificates.show', $certificate)
                ->with('error', 'Only suspended certificates can be reinstated.');
        }

        // Update status based on expiry date
        $certificate->updateStatus();

        // Notify quality managers about reinstatement
        $this->notifyQualityTeam($certificate, 'reinstated');

        return redirect()
            ->route('certificates.show', $certificate)
            ->with('success', 'Certificate reinstated successfully.');
    }

    /**
     * Notify quality team about certificate status changes.
     */
    protected function notifyQualityTeam(Certificate $certificate, string $action): void
    {
        $qualityManagers = User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Quality Manager', 'Admin', 'Super Admin']);
            })
            ->get();

        foreach ($qualityManagers as $manager) {
            $manager->notify(new CertificateStatusChangedNotification($certificate, $action));
        }
    }
}
