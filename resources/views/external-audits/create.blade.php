@extends('layouts/layoutMaster')

@section('title', 'Schedule External Audit')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('external-audits.index') }}">External Audits</a></li>
                <li class="breadcrumb-item active">Schedule New Audit</li>
            </ol>
        </nav>
        <h1 class="h3">Schedule External Audit</h1>
        <p class="text-muted">Schedule a new external certification or surveillance audit</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('external-audits.store') }}" method="POST">
                        @csrf

                        <!-- Audit Information Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Audit Information</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="audit_type" class="form-label required">Audit Type</label>
                                    <select name="audit_type"
                                            id="audit_type"
                                            class="form-select @error('audit_type') is-invalid @enderror"
                                            required>
                                        <option value="">Select audit type...</option>
                                        <option value="initial_certification" {{ old('audit_type') === 'initial_certification' ? 'selected' : '' }}>
                                            Initial Certification
                                        </option>
                                        <option value="surveillance" {{ old('audit_type') === 'surveillance' ? 'selected' : '' }}>
                                            Surveillance Audit
                                        </option>
                                        <option value="recertification" {{ old('audit_type') === 'recertification' ? 'selected' : '' }}>
                                            Recertification Audit
                                        </option>
                                        <option value="special" {{ old('audit_type') === 'special' ? 'selected' : '' }}>
                                            Special Audit
                                        </option>
                                        <option value="follow_up" {{ old('audit_type') === 'follow_up' ? 'selected' : '' }}>
                                            Follow-up Audit
                                        </option>
                                    </select>
                                    @error('audit_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="standard" class="form-label required">Standard</label>
                                    <input type="text"
                                           name="standard"
                                           id="standard"
                                           class="form-control @error('standard') is-invalid @enderror"
                                           value="{{ old('standard') }}"
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
                                           value="{{ old('certification_body') }}"
                                           placeholder="e.g., BSI, TUV, SGS"
                                           required>
                                    @error('certification_body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="coordinator_id" class="form-label">Internal Coordinator</label>
                                    <select name="coordinator_id"
                                            id="coordinator_id"
                                            class="form-select @error('coordinator_id') is-invalid @enderror">
                                        <option value="">Select coordinator...</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('coordinator_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('coordinator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Internal staff member coordinating this audit</small>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Auditor Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Lead Auditor Information</h5>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="lead_auditor_name" class="form-label required">Lead Auditor Name</label>
                                    <input type="text"
                                           name="lead_auditor_name"
                                           id="lead_auditor_name"
                                           class="form-control @error('lead_auditor_name') is-invalid @enderror"
                                           value="{{ old('lead_auditor_name') }}"
                                           required>
                                    @error('lead_auditor_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="lead_auditor_email" class="form-label">Lead Auditor Email</label>
                                    <input type="email"
                                           name="lead_auditor_email"
                                           id="lead_auditor_email"
                                           class="form-control @error('lead_auditor_email') is-invalid @enderror"
                                           value="{{ old('lead_auditor_email') }}">
                                    @error('lead_auditor_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="lead_auditor_phone" class="form-label">Lead Auditor Phone</label>
                                    <input type="text"
                                           name="lead_auditor_phone"
                                           id="lead_auditor_phone"
                                           class="form-control @error('lead_auditor_phone') is-invalid @enderror"
                                           value="{{ old('lead_auditor_phone') }}">
                                    @error('lead_auditor_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Audit Schedule</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="scheduled_start_date" class="form-label required">Scheduled Start Date</label>
                                    <input type="date"
                                           name="scheduled_start_date"
                                           id="scheduled_start_date"
                                           class="form-control @error('scheduled_start_date') is-invalid @enderror"
                                           value="{{ old('scheduled_start_date') }}"
                                           required>
                                    @error('scheduled_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="scheduled_end_date" class="form-label required">Scheduled End Date</label>
                                    <input type="date"
                                           name="scheduled_end_date"
                                           id="scheduled_end_date"
                                           class="form-control @error('scheduled_end_date') is-invalid @enderror"
                                           value="{{ old('scheduled_end_date') }}"
                                           required>
                                    @error('scheduled_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Scope Section -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Audit Scope</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="audited_departments" class="form-label">Departments to be Audited</label>
                                    <select name="audited_departments[]"
                                            id="audited_departments"
                                            class="form-select @error('audited_departments') is-invalid @enderror"
                                            multiple
                                            size="5">
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                                {{ in_array($department->id, old('audited_departments', [])) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('audited_departments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="audited_processes" class="form-label">Processes to be Audited</label>
                                    <textarea name="audited_processes"
                                              id="audited_processes"
                                              class="form-control @error('audited_processes') is-invalid @enderror"
                                              rows="5"
                                              placeholder="Enter processes, one per line">{{ old('audited_processes') }}</textarea>
                                    @error('audited_processes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Enter each process on a new line</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="scope_description" class="form-label">Scope Description</label>
                                    <textarea name="scope_description"
                                              id="scope_description"
                                              class="form-control @error('scope_description') is-invalid @enderror"
                                              rows="4"
                                              placeholder="Detailed description of the audit scope...">{{ old('scope_description') }}</textarea>
                                    @error('scope_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('external-audits.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="icon-base ti tabler-calendar-plus me-1"></i>Schedule Audit
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
                        <i class="icon-base ti tabler-info-circle text-primary"></i> Scheduling Guidelines
                    </h6>
                    <ul class="small mb-0">
                        <li class="mb-2">Select the appropriate audit type based on your certification stage</li>
                        <li class="mb-2">Ensure all lead auditor contact information is accurate</li>
                        <li class="mb-2">Coordinate with departments to minimize operational disruption</li>
                        <li class="mb-2">Allow adequate time between scheduled dates for thorough audit</li>
                        <li>Assign an internal coordinator to facilitate the audit process</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="icon-base ti tabler-bookmark text-info"></i> Audit Types
                    </h6>
                    <div class="small">
                        <div class="mb-2">
                            <strong>Initial Certification:</strong> First-time certification audit
                        </div>
                        <div class="mb-2">
                            <strong>Surveillance:</strong> Regular monitoring audit (typically annual)
                        </div>
                        <div class="mb-2">
                            <strong>Recertification:</strong> Certificate renewal audit (typically every 3 years)
                        </div>
                        <div class="mb-2">
                            <strong>Special:</strong> Unscheduled audit due to specific circumstances
                        </div>
                        <div>
                            <strong>Follow-up:</strong> Audit to verify corrective actions
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Convert processes textarea to array on form submit
    $('form').on('submit', function() {
        const processesTextarea = $('#audited_processes');
        if (processesTextarea.val()) {
            const processes = processesTextarea.val().split('\n').filter(p => p.trim());
            processesTextarea.val(JSON.stringify(processes));
        }
    });

    // Validate end date is after start date
    $('#scheduled_start_date, #scheduled_end_date').on('change', function() {
        const startDate = new Date($('#scheduled_start_date').val());
        const endDate = new Date($('#scheduled_end_date').val());

        if (startDate && endDate && endDate < startDate) {
            $('#scheduled_end_date')[0].setCustomValidity('End date must be after start date');
        } else {
            $('#scheduled_end_date')[0].setCustomValidity('');
        }
    });
});
</script>
@endpush
@endsection
