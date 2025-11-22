@extends('layouts/layoutMaster')

@section('title', 'Edit Certificate')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}">Certificates</a></li>
                <li class="breadcrumb-item"><a href="{{ route('certificates.show', $certificate) }}">{{ $certificate->certificate_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h3">Edit Certificate</h1>
        <p class="text-muted">Update certificate details for {{ $certificate->certificate_number }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('certificates.update', $certificate) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                           value="{{ old('certificate_number', $certificate->certificate_number) }}"
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
                                        <option value="initial" {{ old('certificate_type', $certificate->certificate_type) === 'initial' ? 'selected' : '' }}>
                                            Initial Certification
                                        </option>
                                        <option value="renewal" {{ old('certificate_type', $certificate->certificate_type) === 'renewal' ? 'selected' : '' }}>
                                            Renewal
                                        </option>
                                        <option value="transfer" {{ old('certificate_type', $certificate->certificate_type) === 'transfer' ? 'selected' : '' }}>
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
                                           value="{{ old('standard', $certificate->standard) }}"
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
                                           value="{{ old('certification_body', $certificate->certification_body) }}"
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
                                           value="{{ old('issue_date', $certificate->issue_date?->format('Y-m-d')) }}"
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
                                           value="{{ old('expiry_date', $certificate->expiry_date?->format('Y-m-d')) }}"
                                           required>
                                    @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label required">Status</label>
                                    <select name="status"
                                            id="status"
                                            class="form-select @error('status') is-invalid @enderror"
                                            required>
                                        <option value="valid" {{ old('status', $certificate->status) === 'valid' ? 'selected' : '' }}>Valid</option>
                                        <option value="expiring_soon" {{ old('status', $certificate->status) === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                        <option value="expired" {{ old('status', $certificate->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="suspended" {{ old('status', $certificate->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="revoked" {{ old('status', $certificate->status) === 'revoked' ? 'selected' : '' }}>Revoked</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Status is automatically managed based on expiry date</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="issued_for_audit_id" class="form-label">Link to External Audit</label>
                                    <select name="issued_for_audit_id"
                                            id="issued_for_audit_id"
                                            class="form-select @error('issued_for_audit_id') is-invalid @enderror">
                                        <option value="">No linked audit</option>
                                        @foreach($audits as $audit)
                                        <option value="{{ $audit->id }}" {{ old('issued_for_audit_id', $certificate->issued_for_audit_id) == $audit->id ? 'selected' : '' }}>
                                            {{ $audit->audit_number }} - {{ $audit->standard }} ({{ $audit->actual_end_date->format('M d, Y') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('issued_for_audit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                              required>{{ old('scope_of_certification', $certificate->scope_of_certification) }}</textarea>
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
                                              placeholder="Enter sites, one per line">{{ old('covered_sites', is_array($certificate->covered_sites) ? implode("\n", $certificate->covered_sites) : '') }}</textarea>
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
                                              placeholder="Enter processes, one per line">{{ old('covered_processes', is_array($certificate->covered_processes) ? implode("\n", $certificate->covered_processes) : '') }}</textarea>
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
                                              placeholder="Any additional notes or special conditions...">{{ old('notes', $certificate->notes) }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>Update Certificate
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
                        <i class="icon-base ti tabler-info-circle text-primary"></i> Edit Guidelines
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">Only valid or expiring certificates can be edited</li>
                        <li class="mb-2">Ensure dates match the official certificate document</li>
                        <li class="mb-2">Status will be automatically updated based on expiry date</li>
                        <li class="mb-2">Changes are logged with timestamp and user information</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-alert-triangle text-warning"></i> Important
                    </h6>
                    <p class="small mb-0">
                        Certificate information should match the official documentation exactly.
                        Any discrepancies may cause issues during audits or recertification processes.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">Certificate Status</h6>
                    <p class="mb-2">
                        <span class="badge bg-{{ $certificate->status_color }}">
                            {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                        </span>
                    </p>
                    <p class="small text-muted mb-0">
                        Last updated: {{ $certificate->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            @if($certificate->isExpiringSoon())
            <div class="card border-0 shadow-sm mt-3 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="icon-base ti tabler-alert-triangle"></i> Expiry Warning
                    </h6>
                    <p class="small mb-0">
                        This certificate expires in <strong>{{ $certificate->days_until_expiry }} days</strong>.
                        Please schedule a recertification audit soon.
                    </p>
                </div>
            </div>
            @endif
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
});
</script>
@endpush
@endsection
