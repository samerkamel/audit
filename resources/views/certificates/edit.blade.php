@extends('layouts/layoutMaster')

@section('title', __('Edit Certificate'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}">{{ __('Certificates') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('certificates.show', $certificate) }}">{{ $certificate->certificate_number }}</a></li>
                <li class="breadcrumb-item active">{{ __('Edit') }}</li>
            </ol>
        </nav>
        <h1 class="h3">{{ __('Edit Certificate') }}</h1>
        <p class="text-muted">{{ __('Update certificate details for') }} {{ $certificate->certificate_number }}</p>
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
                            <h5 class="border-bottom pb-2 mb-3">{{ __('Certificate Information') }}</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="certificate_number" class="form-label required">{{ __('Certificate Number') }}</label>
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
                                    <label for="certificate_type" class="form-label required">{{ __('Certificate Type') }}</label>
                                    <select name="certificate_type"
                                            id="certificate_type"
                                            class="form-select @error('certificate_type') is-invalid @enderror"
                                            required>
                                        <option value="">{{ __('Select type...') }}</option>
                                        <option value="initial" {{ old('certificate_type', $certificate->certificate_type) === 'initial' ? 'selected' : '' }}>
                                            {{ __('Initial Certification') }}
                                        </option>
                                        <option value="renewal" {{ old('certificate_type', $certificate->certificate_type) === 'renewal' ? 'selected' : '' }}>
                                            {{ __('Renewal') }}
                                        </option>
                                        <option value="transfer" {{ old('certificate_type', $certificate->certificate_type) === 'transfer' ? 'selected' : '' }}>
                                            {{ __('Transfer') }}
                                        </option>
                                    </select>
                                    @error('certificate_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="standard" class="form-label required">{{ __('Standard') }}</label>
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
                                    <label for="certification_body" class="form-label required">{{ __('Certification Body') }}</label>
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
                                    <label for="issue_date" class="form-label required">{{ __('Issue Date') }}</label>
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
                                    <label for="expiry_date" class="form-label required">{{ __('Expiry Date') }}</label>
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
                                    <label for="status" class="form-label required">{{ __('Status') }}</label>
                                    <select name="status"
                                            id="status"
                                            class="form-select @error('status') is-invalid @enderror"
                                            required>
                                        <option value="valid" {{ old('status', $certificate->status) === 'valid' ? 'selected' : '' }}>{{ __('Valid') }}</option>
                                        <option value="expiring_soon" {{ old('status', $certificate->status) === 'expiring_soon' ? 'selected' : '' }}>{{ __('Expiring Soon') }}</option>
                                        <option value="expired" {{ old('status', $certificate->status) === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                        <option value="suspended" {{ old('status', $certificate->status) === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                        <option value="revoked" {{ old('status', $certificate->status) === 'revoked' ? 'selected' : '' }}>{{ __('Revoked') }}</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Status is automatically managed based on expiry date') }}</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="issued_for_audit_id" class="form-label">{{ __('Link to External Audit') }}</label>
                                    <select name="issued_for_audit_id"
                                            id="issued_for_audit_id"
                                            class="form-select @error('issued_for_audit_id') is-invalid @enderror">
                                        <option value="">{{ __('No linked audit') }}</option>
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
                            <h5 class="border-bottom pb-2 mb-3">{{ __('Certification Scope') }}</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="scope_of_certification" class="form-label required">{{ __('Scope of Certification') }}</label>
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
                                    <label for="covered_sites" class="form-label">{{ __('Covered Sites') }}</label>
                                    <textarea name="covered_sites"
                                              id="covered_sites"
                                              class="form-control @error('covered_sites') is-invalid @enderror"
                                              rows="3"
                                              placeholder="{{ __('Enter sites, one per line') }}">{{ old('covered_sites', is_array($certificate->covered_sites) ? implode("\n", $certificate->covered_sites) : '') }}</textarea>
                                    @error('covered_sites')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Enter each site on a new line') }}</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="covered_processes" class="form-label">{{ __('Covered Processes') }}</label>
                                    <textarea name="covered_processes"
                                              id="covered_processes"
                                              class="form-control @error('covered_processes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="{{ __('Enter processes, one per line') }}">{{ old('covered_processes', is_array($certificate->covered_processes) ? implode("\n", $certificate->covered_processes) : '') }}</textarea>
                                    @error('covered_processes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Enter each process on a new line') }}</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                                    <textarea name="notes"
                                              id="notes"
                                              class="form-control @error('notes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="{{ __('Any additional notes or special conditions...') }}">{{ old('notes', $certificate->notes) }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('certificates.show', $certificate) }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>{{ __('Update Certificate') }}
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
                        <i class="icon-base ti tabler-info-circle text-primary"></i> {{ __('Edit Guidelines') }}
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">{{ __('Only valid or expiring certificates can be edited') }}</li>
                        <li class="mb-2">{{ __('Ensure dates match the official certificate document') }}</li>
                        <li class="mb-2">{{ __('Status will be automatically updated based on expiry date') }}</li>
                        <li class="mb-2">{{ __('Changes are logged with timestamp and user information') }}</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-alert-triangle text-warning"></i> {{ __('Important') }}
                    </h6>
                    <p class="small mb-0">
                        {{ __('Certificate information should match the official documentation exactly. Any discrepancies may cause issues during audits or recertification processes.') }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">{{ __('Certificate Status') }}</h6>
                    <p class="mb-2">
                        <span class="badge bg-{{ $certificate->status_color }}">
                            {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                        </span>
                    </p>
                    <p class="small text-muted mb-0">
                        {{ __('Last updated') }}: {{ $certificate->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            @if($certificate->isExpiringSoon())
            <div class="card border-0 shadow-sm mt-3 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="icon-base ti tabler-alert-triangle"></i> {{ __('Expiry Warning') }}
                    </h6>
                    <p class="small mb-0">
                        {{ __('This certificate expires in') }} <strong>{{ $certificate->days_until_expiry }} {{ __('days') }}</strong>.
                        {{ __('Please schedule a recertification audit soon.') }}
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
            $('#expiry_date')[0].setCustomValidity('{{ __('Expiry date must be after issue date') }}');
        } else {
            $('#expiry_date')[0].setCustomValidity('');
        }
    });
});
</script>
@endpush
@endsection
