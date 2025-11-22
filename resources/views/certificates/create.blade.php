@extends('layouts/layoutMaster')

@section('title', 'Register Certificate')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}">Certificates</a></li>
                <li class="breadcrumb-item active">Register New Certificate</li>
            </ol>
        </nav>
        <h1 class="h3">Register ISO Certificate</h1>
        <p class="text-muted">Register a new certification document</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('certificates.store') }}" method="POST">
                        @csrf

                        <!-- Certificate Information Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Certificate Information</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="certificate_number" class="form-label required">Certificate Number</label>
                                    <input type="text"
                                           name="certificate_number"
                                           id="certificate_number"
                                           class="form-control @error('certificate_number') is-invalid @enderror"
                                           value="{{ old('certificate_number') }}"
                                           placeholder="e.g., CERT-12345"
                                           required>
                                    @error('certificate_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="certificate_type" class="form-label required">Certificate Type</label>
                                    <select name="certificate_type"
                                            id="certificate_type"
                                            class="form-select @error('certificate_type') is-invalid @enderror"
                                            required>
                                        <option value="">Select type...</option>
                                        <option value="initial" {{ old('certificate_type') === 'initial' ? 'selected' : '' }}>
                                            Initial Certification
                                        </option>
                                        <option value="renewal" {{ old('certificate_type') === 'renewal' ? 'selected' : '' }}>
                                            Renewal
                                        </option>
                                        <option value="transfer" {{ old('certificate_type') === 'transfer' ? 'selected' : '' }}>
                                            Transfer
                                        </option>
                                    </select>
                                    @error('certificate_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="standard" class="form-label required">Standard</label>
                                    <input type="text"
                                           name="standard"
                                           id="standard"
                                           class="form-control @error('standard') is-invalid @enderror"
                                           value="{{ old('standard', $audit?->standard) }}"
                                           placeholder="e.g., ISO 9001:2015"
                                           required>
                                    @error('standard')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="certification_body" class="form-label required">Certification Body</label>
                                    <input type="text"
                                           name="certification_body"
                                           id="certification_body"
                                           class="form-control @error('certification_body') is-invalid @enderror"
                                           value="{{ old('certification_body', $audit?->certification_body) }}"
                                           placeholder="e.g., BSI, TUV, SGS"
                                           required>
                                    @error('certification_body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="issue_date" class="form-label required">Issue Date</label>
                                    <input type="date"
                                           name="issue_date"
                                           id="issue_date"
                                           class="form-control @error('issue_date') is-invalid @enderror"
                                           value="{{ old('issue_date', $audit?->actual_end_date?->format('Y-m-d')) }}"
                                           required>
                                    @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label required">Expiry Date</label>
                                    <input type="date"
                                           name="expiry_date"
                                           id="expiry_date"
                                           class="form-control @error('expiry_date') is-invalid @enderror"
                                           value="{{ old('expiry_date') }}"
                                           required>
                                    @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if($audit)
                                <input type="hidden" name="issued_for_audit_id" value="{{ $audit->id }}">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="icon-base ti tabler-info-circle"></i>
                                        This certificate will be linked to audit <strong>{{ $audit->audit_number }}</strong>
                                    </div>
                                </div>
                                @else
                                <div class="col-md-12">
                                    <label for="issued_for_audit_id" class="form-label">Link to External Audit (Optional)</label>
                                    <select name="issued_for_audit_id"
                                            id="issued_for_audit_id"
                                            class="form-select @error('issued_for_audit_id') is-invalid @enderror">
                                        <option value="">No linked audit</option>
                                        @foreach($audits as $auditOption)
                                        <option value="{{ $auditOption->id }}" {{ old('issued_for_audit_id') == $auditOption->id ? 'selected' : '' }}>
                                            {{ $auditOption->audit_number }} - {{ $auditOption->standard }} ({{ $auditOption->actual_end_date->format('M d, Y') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('issued_for_audit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Scope Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Certification Scope</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="scope_of_certification" class="form-label required">Scope of Certification</label>
                                    <textarea name="scope_of_certification"
                                              id="scope_of_certification"
                                              class="form-control @error('scope_of_certification') is-invalid @enderror"
                                              rows="4"
                                              placeholder="Detailed description of the certification scope..."
                                              required>{{ old('scope_of_certification', $audit?->scope_description) }}</textarea>
                                    @error('scope_of_certification')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="covered_sites" class="form-label">Covered Sites</label>
                                    <textarea name="covered_sites"
                                              id="covered_sites"
                                              class="form-control @error('covered_sites') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Enter sites, one per line">{{ old('covered_sites') }}</textarea>
                                    @error('covered_sites')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter each site on a new line</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="covered_processes" class="form-label">Covered Processes</label>
                                    <textarea name="covered_processes"
                                              id="covered_processes"
                                              class="form-control @error('covered_processes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Enter processes, one per line">{{ old('covered_processes', $audit && is_array($audit->audited_processes) ? implode("\n", $audit->audited_processes) : '') }}</textarea>
                                    @error('covered_processes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter each process on a new line</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea name="notes"
                                              id="notes"
                                              class="form-control @error('notes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Any additional notes or special conditions...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('certificates.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-award me-1"></i>Register Certificate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-info-circle text-primary"></i> Registration Guidelines
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">Enter the exact certificate number from the official document</li>
                        <li class="mb-2">Ensure dates match the official certificate exactly</li>
                        <li class="mb-2">Link to the audit that resulted in this certificate if applicable</li>
                        <li class="mb-2">Describe the complete scope of certification</li>
                        <li>Certificate status will be automatically managed based on expiry date</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-bookmark text-info"></i> Certificate Types
                    </h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong>Initial:</strong> First-time certification for this standard
                        </div>
                        <div class="mb-2">
                            <strong>Renewal:</strong> Certificate renewal after expiry (typically 3 years)
                        </div>
                        <div>
                            <strong>Transfer:</strong> Certificate transferred from another certification body
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-clock text-warning"></i> Validity Tracking
                    </h6>
                    <p class="small mb-0">
                        The system will automatically:
                        <ul class="small mb-0">
                            <li>Mark certificates as "expiring soon" 90 days before expiry</li>
                            <li>Mark certificates as "expired" after the expiry date</li>
                            <li>Send notifications for expiring certificates</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Convert arrays to JSON on form submit
    $('form').on('submit', function() {
        const sitesTextarea = $('#covered_sites');
        if (sitesTextarea.val()) {
            const sites = sitesTextarea.val().split('\n').filter(s => s.trim());
            sitesTextarea.val(JSON.stringify(sites));
        }

        const processesTextarea = $('#covered_processes');
        if (processesTextarea.val()) {
            const processes = processesTextarea.val().split('\n').filter(p => p.trim());
            processesTextarea.val(JSON.stringify(processes));
        }
    });

    // Validate expiry date is after issue date
    $('#issue_date, #expiry_date').on('change', function() {
        const issueDate = new Date($('#issue_date').val());
        const expiryDate = new Date($('#expiry_date').val());

        if (issueDate && expiryDate && expiryDate <= issueDate) {
            $('#expiry_date')[0].setCustomValidity('Expiry date must be after issue date');
        } else {
            $('#expiry_date')[0].setCustomValidity('');
        }
    });

    // Auto-calculate typical 3-year expiry when issue date is set
    $('#issue_date').on('change', function() {
        if (!$('#expiry_date').val()) {
            const issueDate = new Date($(this).val());
            const expiryDate = new Date(issueDate);
            expiryDate.setFullYear(expiryDate.getFullYear() + 3);
            $('#expiry_date').val(expiryDate.toISOString().split('T')[0]);
        }
    });
});
</script>
@endpush
@endsection
