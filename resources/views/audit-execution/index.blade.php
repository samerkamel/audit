@extends('layouts/layoutMaster')

@section('title', 'My Audit Assignments')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">
              <i class="icon-base ti tabler-checkbox me-2"></i>My Audit Assignments
            </h4>
            <p class="text-muted mb-0">Audit plans you are assigned to as lead auditor or team member</p>
          </div>
        </div>
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
              <a href="{{ route('audit-execution.show', $plan) }}" class="btn btn-primary btn-sm">
                <i class="icon-base ti tabler-arrow-right me-1"></i> Start Audit
              </a>
            </div>
          </div>

          @if($plan->description)
            <p class="text-muted mb-3">{{ $plan->description }}</p>
          @endif

          <!-- Progress Bar -->
          <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted small">Overall Progress</span>
              <span class="fw-semibold">{{ $plan->progress_percentage }}%</span>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar" role="progressbar"
                style="width: {{ $plan->progress_percentage }}%;"
                aria-valuenow="{{ $plan->progress_percentage }}"
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
              <i class="icon-base ti tabler-clipboard-off" style="font-size: 2rem;"></i>
            </span>
          </div>
          <h5 class="mb-1">No Audit Assignments</h5>
          <p class="text-muted mb-4">You don't have any audit plans assigned at the moment.</p>
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
