@extends('layouts/layoutMaster')

@section('title', 'Audit Reports')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">
              <i class="icon-base ti tabler-report-analytics me-2"></i>Audit Reports
            </h4>
            <p class="text-muted mb-0">View audit results and compliance statistics</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" action="{{ route('audit-reports.index') }}">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="audit_type" class="form-label">Audit Type</label>
              <select name="audit_type" id="audit_type" class="form-select">
                <option value="">All Types</option>
                <option value="internal" {{ request('audit_type') == 'internal' ? 'selected' : '' }}>Internal Audit</option>
                <option value="external" {{ request('audit_type') == 'external' ? 'selected' : '' }}>External Audit</option>
                <option value="surveillance" {{ request('audit_type') == 'surveillance' ? 'selected' : '' }}>Surveillance Audit</option>
                <option value="certification" {{ request('audit_type') == 'certification' ? 'selected' : '' }}>Certification Audit</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">
                <i class="icon-base ti tabler-filter me-1"></i> Apply Filters
              </button>
              <a href="{{ route('audit-reports.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Clear
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Audit Plans List -->
    @forelse($auditPlans as $plan)
      <div class="card mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h5 class="mb-2">{{ $plan->title }}</h5>
              <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge bg-label-{{ $plan->status_color }}">{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span>
                <span class="badge bg-label-info">{{ $plan->audit_type_label }}</span>
              </div>
            </div>
            <div class="text-end">
              <a href="{{ route('audit-reports.show', $plan) }}" class="btn btn-primary btn-sm">
                <i class="icon-base ti tabler-eye me-1"></i> View Report
              </a>
            </div>
          </div>

          @if($plan->description)
            <p class="text-muted mb-3">{{ $plan->description }}</p>
          @endif

          <!-- Statistics Grid -->
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="avatar avatar-sm mx-auto mb-2">
                  <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="icon-base ti tabler-list-check"></i>
                  </span>
                </div>
                <small class="text-muted d-block">Total Responses</small>
                <h5 class="mb-0">{{ $plan->total_responses }}</h5>
              </div>
            </div>

            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="avatar avatar-sm mx-auto mb-2">
                  <span class="avatar-initial rounded-circle bg-label-success">
                    <i class="icon-base ti tabler-check"></i>
                  </span>
                </div>
                <small class="text-muted d-block">Complied</small>
                <h5 class="mb-0 text-success">{{ $plan->complied_count }}</h5>
              </div>
            </div>

            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="avatar avatar-sm mx-auto mb-2">
                  <span class="avatar-initial rounded-circle bg-label-danger">
                    <i class="icon-base ti tabler-x"></i>
                  </span>
                </div>
                <small class="text-muted d-block">Not Complied</small>
                <h5 class="mb-0 text-danger">{{ $plan->not_complied_count }}</h5>
              </div>
            </div>

            <div class="col-md-3">
              <div class="border rounded p-3 text-center">
                <div class="avatar avatar-sm mx-auto mb-2">
                  <span class="avatar-initial rounded-circle bg-label-secondary">
                    <i class="icon-base ti tabler-minus"></i>
                  </span>
                </div>
                <small class="text-muted d-block">Not Applicable</small>
                <h5 class="mb-0">{{ $plan->not_applicable_count }}</h5>
              </div>
            </div>
          </div>

          <!-- Compliance Percentage -->
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted small">Compliance Rate</span>
              <span class="fw-semibold">{{ $plan->compliance_percentage }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar
                @if($plan->compliance_percentage >= 90) bg-success
                @elseif($plan->compliance_percentage >= 70) bg-warning
                @else bg-danger
                @endif"
                role="progressbar"
                style="width: {{ $plan->compliance_percentage }}%;"
                aria-valuenow="{{ $plan->compliance_percentage }}"
                aria-valuemin="0"
                aria-valuemax="100">
              </div>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="row g-3">
            <div class="col-md-4">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded-circle bg-label-primary">
                    <i class="icon-base ti tabler-user"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Lead Auditor</small>
                  <span class="fw-semibold">{{ $plan->leadAuditor->name }}</span>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded-circle bg-label-info">
                    <i class="icon-base ti tabler-building-community"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Departments</small>
                  <span class="fw-semibold">{{ $plan->departments->count() }}</span>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded-circle bg-label-success">
                    <i class="icon-base ti tabler-calendar"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Created</small>
                  <span class="fw-semibold">{{ $plan->created_at->format('M d, Y') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="card">
        <div class="card-body text-center py-6">
          <div class="avatar avatar-xl mx-auto mb-3">
            <span class="avatar-initial rounded-circle bg-label-secondary">
              <i class="icon-base ti tabler-report-off" style="font-size: 2rem;"></i>
            </span>
          </div>
          <h5 class="mb-1">No Audit Reports</h5>
          <p class="text-muted mb-4">There are no audit plans with data to report yet.</p>
        </div>
      </div>
    @endforelse

    <!-- Pagination -->
    @if($auditPlans->hasPages())
      <div class="card">
        <div class="card-body">
          {{ $auditPlans->links() }}
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
