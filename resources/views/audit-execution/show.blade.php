@extends('layouts/layoutMaster')

@section('title', 'Audit Execution - ' . $auditPlan->title)

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="d-flex align-items-center mb-2">
              <a href="{{ route('audit-execution.index') }}" class="btn btn-sm btn-icon btn-label-secondary me-2">
                <i class="icon-base ti tabler-arrow-left"></i>
              </a>
              <h4 class="mb-0">{{ $auditPlan->title }}</h4>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2">
              <span class="badge bg-label-{{ $auditPlan->status_color }}">{{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}</span>
              <span class="badge bg-label-info">{{ $auditPlan->audit_type_label }}</span>
            </div>
            @if($auditPlan->description)
              <p class="text-muted mb-0">{{ $auditPlan->description }}</p>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Departments & Checklist Groups -->
    @foreach($departments as $department)
      <div class="card mb-4">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-1">
                <i class="icon-base ti tabler-building-community me-1"></i>
                {{ $department->name }}
              </h5>
              <small class="text-muted">{{ $department->code }}</small>
            </div>
          </div>
        </div>

        <div class="card-body">
          @if($department->checklist_groups && $department->checklist_groups->count() > 0)
            <div class="row">
              @foreach($department->checklist_groups as $group)
                <div class="col-md-6 col-lg-4 mb-4">
                  <div class="card h-100 border">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                          <h6 class="mb-1">{{ $group->code }}</h6>
                          <p class="mb-2">{{ $group->title }}</p>
                          @if($group->quality_procedure_reference)
                            <span class="badge bg-label-secondary mb-2">{{ $group->quality_procedure_reference }}</span>
                          @endif
                        </div>
                      </div>

                      <!-- Progress -->
                      <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                          <small class="text-muted">Progress</small>
                          <small class="fw-semibold">{{ $group->answered_questions }}/{{ $group->total_questions }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                          <div class="progress-bar @if($group->progress_percentage == 100) bg-success @endif"
                            role="progressbar"
                            style="width: {{ $group->progress_percentage }}%;"
                            aria-valuenow="{{ $group->progress_percentage }}"
                            aria-valuemin="0"
                            aria-valuemax="100">
                          </div>
                        </div>
                      </div>

                      <!-- Action Button -->
                      <a href="{{ route('audit-execution.execute', [$auditPlan, $department, $group]) }}"
                        class="btn btn-sm w-100 @if($group->progress_percentage == 100) btn-outline-success @else btn-primary @endif">
                        <i class="icon-base ti tabler-clipboard-check me-1"></i>
                        @if($group->progress_percentage == 100)
                          Review Audit
                        @elseif($group->progress_percentage > 0)
                          Continue Audit
                        @else
                          Start Audit
                        @endif
                      </a>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-warning mb-0">
              <i class="icon-base ti tabler-alert-triangle me-2"></i>
              No checklist groups assigned to this department yet.
            </div>
          @endif
        </div>
      </div>
    @endforeach

    @if($departments->count() == 0)
      <div class="card">
        <div class="card-body text-center py-6">
          <div class="avatar avatar-xl mx-auto mb-3">
            <span class="avatar-initial rounded-circle bg-label-warning">
              <i class="icon-base ti tabler-alert-circle" style="font-size: 2rem;"></i>
            </span>
          </div>
          <h5 class="mb-1">No Departments</h5>
          <p class="text-muted mb-0">This audit plan doesn't have any departments assigned yet.</p>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
