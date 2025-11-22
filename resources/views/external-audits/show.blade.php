@extends('layouts/layoutMaster')

@section('title', __('External Audit Details'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('external-audits.index') }}">{{ __('External Audits') }}</a></li>
                <li class="breadcrumb-item active">{{ $externalAudit->audit_number }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3 mb-1">{{ $externalAudit->audit_number }}</h1>
                <p class="text-muted mb-0">
                    {{ $externalAudit->audit_type_label }} - {{ $externalAudit->standard }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-{{ $externalAudit->status_color }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $externalAudit->status)) }}
                </span>
                <span class="badge bg-{{ $externalAudit->result_color }} fs-6">
                    {{ ucfirst($externalAudit->result) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Audit Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Audit Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Audit Type') }}</label>
                            <p class="mb-0">{{ $externalAudit->audit_type_label }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Standard') }}</label>
                            <p class="mb-0">{{ $externalAudit->standard }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Certification Body') }}</label>
                            <p class="mb-0">{{ $externalAudit->certification_body }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Internal Coordinator') }}</label>
                            <p class="mb-0">
                                @if($externalAudit->coordinator)
                                {{ $externalAudit->coordinator->name }}
                                @else
                                <span class="text-muted">{{ __('Not assigned') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lead Auditor Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Lead Auditor') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="text-muted small">{{ __('Name') }}</label>
                            <p class="mb-0">{{ $externalAudit->lead_auditor_name }}</p>
                        </div>
                        @if($externalAudit->lead_auditor_email)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Email') }}</label>
                            <p class="mb-0">
                                <a href="mailto:{{ $externalAudit->lead_auditor_email }}">
                                    {{ $externalAudit->lead_auditor_email }}
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($externalAudit->lead_auditor_phone)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Phone') }}</label>
                            <p class="mb-0">
                                <a href="tel:{{ $externalAudit->lead_auditor_phone }}">
                                    {{ $externalAudit->lead_auditor_phone }}
                                </a>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Schedule') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Scheduled Start Date') }}</label>
                            <p class="mb-0">{{ $externalAudit->scheduled_start_date->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Scheduled End Date') }}</label>
                            <p class="mb-0">{{ $externalAudit->scheduled_end_date->format('F d, Y') }}</p>
                        </div>
                        @if($externalAudit->actual_start_date)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Actual Start Date') }}</label>
                            <p class="mb-0">{{ $externalAudit->actual_start_date->format('F d, Y') }}</p>
                        </div>
                        @endif
                        @if($externalAudit->actual_end_date)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Actual End Date') }}</label>
                            <p class="mb-0">{{ $externalAudit->actual_end_date->format('F d, Y') }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Duration') }}</label>
                            <p class="mb-0">{{ $externalAudit->duration_in_days }} {{ __('day(s)') }}</p>
                        </div>
                        @if($externalAudit->next_audit_date)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Next Audit Date') }}</label>
                            <p class="mb-0">{{ $externalAudit->next_audit_date->format('F d, Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scope Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Audit Scope') }}</h5>
                </div>
                <div class="card-body">
                    @if($externalAudit->scope_description)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Scope Description') }}</label>
                        <p class="mb-0">{{ $externalAudit->scope_description }}</p>
                    </div>
                    @endif

                    @if($externalAudit->audited_departments && count($externalAudit->audited_departments) > 0)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Audited Departments') }}</label>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($externalAudit->audited_departments as $deptId)
                            @php
                            $dept = \App\Models\Department::find($deptId);
                            @endphp
                            @if($dept)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $dept->name }}</span>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($externalAudit->audited_processes && count($externalAudit->audited_processes) > 0)
                    <div>
                        <label class="text-muted small">{{ __('Audited Processes') }}</label>
                        <ul class="mb-0">
                            @foreach($externalAudit->audited_processes as $process)
                            <li>{{ $process }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Results & Findings (only if completed) -->
            @if($externalAudit->status === 'completed')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Audit Results') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Findings Summary -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <h3 class="mb-0 text-danger">{{ $externalAudit->major_ncrs_count }}</h3>
                                <small class="text-muted">{{ __('Major NCRs') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <h3 class="mb-0 text-warning">{{ $externalAudit->minor_ncrs_count }}</h3>
                                <small class="text-muted">{{ __('Minor NCRs') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="mb-0 text-info">{{ $externalAudit->observations_count }}</h3>
                                <small class="text-muted">{{ __('Observations') }}</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="mb-0 text-success">{{ $externalAudit->opportunities_count }}</h3>
                                <small class="text-muted">{{ __('Opportunities') }}</small>
                            </div>
                        </div>
                    </div>

                    @if($externalAudit->audit_summary)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Audit Summary') }}</label>
                        <p class="mb-0">{{ $externalAudit->audit_summary }}</p>
                    </div>
                    @endif

                    @if($externalAudit->strengths)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Strengths') }}</label>
                        <p class="mb-0 text-success">{{ $externalAudit->strengths }}</p>
                    </div>
                    @endif

                    @if($externalAudit->areas_for_improvement)
                    <div>
                        <label class="text-muted small">{{ __('Areas for Improvement') }}</label>
                        <p class="mb-0 text-warning">{{ $externalAudit->areas_for_improvement }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Workflow Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Actions') }}</h5>
                </div>
                <div class="card-body">
                    @can('update', $externalAudit)
                    <!-- Start Audit -->
                    @if($externalAudit->canStart())
                    <form action="{{ route('external-audits.start', $externalAudit) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="icon-base ti tabler-player-play me-1"></i>{{ __('Start Audit') }}
                        </button>
                    </form>
                    @endif

                    <!-- Complete Audit -->
                    @if($externalAudit->canComplete())
                    <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeAuditModal">
                        <i class="icon-base ti tabler-circle-check me-1"></i>{{ __('Complete Audit') }}
                    </button>
                    @endif

                    <!-- Edit Audit -->
                    @if(in_array($externalAudit->status, ['scheduled', 'in_progress']))
                    <a href="{{ route('external-audits.edit', $externalAudit) }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="icon-base ti tabler-edit me-1"></i>{{ __('Edit Details') }}
                    </a>
                    @endif

                    <!-- Cancel Audit -->
                    @if($externalAudit->status !== 'completed' && $externalAudit->status !== 'cancelled')
                    <form action="{{ route('external-audits.cancel', $externalAudit) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to cancel this audit?') }}')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                            <i class="icon-base ti tabler-x me-1"></i>{{ __('Cancel Audit') }}
                        </button>
                    </form>
                    @endif
                    @endcan

                    <!-- Generate Certificate -->
                    @if($externalAudit->canGenerateCertificate())
                    <a href="{{ route('certificates.create', ['audit' => $externalAudit->id]) }}" class="btn btn-success w-100 mb-2">
                        <i class="icon-base ti tabler-award me-1"></i>{{ __('Generate Certificate') }}
                    </a>
                    @endif

                    <!-- View Certificate -->
                    @if($externalAudit->certificate)
                    <a href="{{ route('certificates.show', $externalAudit->certificate) }}" class="btn btn-outline-success w-100 mb-2">
                        <i class="icon-base ti tabler-award-fill me-1"></i>{{ __('View Certificate') }}
                    </a>
                    @endif
                </div>
            </div>

            <!-- Certificate Status -->
            @if($externalAudit->certificate)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Certificate') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ $externalAudit->certificate->certificate_number }}</strong>
                    </p>
                    <p class="mb-2">
                        <span class="badge bg-{{ $externalAudit->certificate->status_color }}">
                            {{ ucfirst(str_replace('_', ' ', $externalAudit->certificate->status)) }}
                        </span>
                    </p>
                    <p class="mb-2 small">
                        <strong>{{ __('Issued:') }}</strong> {{ $externalAudit->certificate->issue_date->format('M d, Y') }}<br>
                        <strong>{{ __('Expires:') }}</strong> {{ $externalAudit->certificate->expiry_date->format('M d, Y') }}
                    </p>
                    @if($externalAudit->certificate->isExpiringSoon())
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="icon-base ti tabler-alert-triangle"></i>
                        {{ __('Expires in') }} {{ $externalAudit->certificate->days_until_expiry }} {{ __('days') }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Details') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <strong>{{ __('Created by:') }}</strong><br>
                        {{ $externalAudit->createdBy->name ?? __('System') }}<br>
                        <span class="text-muted">{{ $externalAudit->created_at->format('M d, Y') }}</span>
                    </p>
                    <p class="mb-0 small">
                        <strong>{{ __('Last updated:') }}</strong><br>
                        <span class="text-muted">{{ $externalAudit->updated_at->diffForHumans() }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Audit Modal -->
<div class="modal fade" id="completeAuditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('external-audits.complete', $externalAudit) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Complete Audit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="actual_end_date" class="form-label required">{{ __('Actual End Date') }}</label>
                            <input type="date" name="actual_end_date" id="actual_end_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="result" class="form-label required">{{ __('Audit Result') }}</label>
                            <select name="result" id="result" class="form-select" required>
                                <option value="">{{ __('Select result...') }}</option>
                                <option value="passed">{{ __('Passed') }}</option>
                                <option value="conditional">{{ __('Conditional') }}</option>
                                <option value="failed">{{ __('Failed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="major_ncrs_count" class="form-label required">{{ __('Major NCRs') }}</label>
                            <input type="number" name="major_ncrs_count" id="major_ncrs_count" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label for="minor_ncrs_count" class="form-label required">{{ __('Minor NCRs') }}</label>
                            <input type="number" name="minor_ncrs_count" id="minor_ncrs_count" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label for="observations_count" class="form-label required">{{ __('Observations') }}</label>
                            <input type="number" name="observations_count" id="observations_count" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label for="opportunities_count" class="form-label required">{{ __('Opportunities') }}</label>
                            <input type="number" name="opportunities_count" id="opportunities_count" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-12">
                            <label for="audit_summary" class="form-label required">{{ __('Audit Summary') }}</label>
                            <textarea name="audit_summary" id="audit_summary" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="strengths" class="form-label">{{ __('Strengths') }}</label>
                            <textarea name="strengths" id="strengths" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="areas_for_improvement" class="form-label">{{ __('Areas for Improvement') }}</label>
                            <textarea name="areas_for_improvement" id="areas_for_improvement" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="next_audit_date" class="form-label">{{ __('Next Audit Date') }}</label>
                            <input type="date" name="next_audit_date" id="next_audit_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Complete Audit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
