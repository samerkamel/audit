@extends('layouts/layoutMaster')

@section('title', 'Audit Report - ' . $auditPlan->title)

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="d-flex align-items-center mb-2">
              <a href="{{ route('audit-reports.index') }}" class="btn btn-sm btn-icon btn-label-secondary me-2">
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
          <div class="text-end">
            <button onclick="window.print()" class="btn btn-label-secondary btn-sm">
              <i class="icon-base ti tabler-printer me-1"></i> Print
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Overall Statistics -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="icon-base ti tabler-chart-bar me-1"></i>
          Overall Compliance Statistics
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="border rounded p-3 text-center">
              <div class="avatar avatar-md mx-auto mb-2">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  <i class="icon-base ti tabler-list-check"></i>
                </span>
              </div>
              <small class="text-muted d-block">Total Responses</small>
              <h4 class="mb-0">{{ $stats['total_responses'] }}</h4>
            </div>
          </div>

          <div class="col-md-3">
            <div class="border rounded p-3 text-center">
              <div class="avatar avatar-md mx-auto mb-2">
                <span class="avatar-initial rounded-circle bg-label-success">
                  <i class="icon-base ti tabler-check"></i>
                </span>
              </div>
              <small class="text-muted d-block">Complied</small>
              <h4 class="mb-0 text-success">{{ $stats['complied'] }}</h4>
            </div>
          </div>

          <div class="col-md-3">
            <div class="border rounded p-3 text-center">
              <div class="avatar avatar-md mx-auto mb-2">
                <span class="avatar-initial rounded-circle bg-label-danger">
                  <i class="icon-base ti tabler-x"></i>
                </span>
              </div>
              <small class="text-muted d-block">Not Complied</small>
              <h4 class="mb-0 text-danger">{{ $stats['not_complied'] }}</h4>
            </div>
          </div>

          <div class="col-md-3">
            <div class="border rounded p-3 text-center">
              <div class="avatar avatar-md mx-auto mb-2">
                <span class="avatar-initial rounded-circle bg-label-secondary">
                  <i class="icon-base ti tabler-minus"></i>
                </span>
              </div>
              <small class="text-muted d-block">Not Applicable</small>
              <h4 class="mb-0">{{ $stats['not_applicable'] }}</h4>
            </div>
          </div>
        </div>

        <!-- Compliance Rate -->
        <div class="text-center">
          <h2 class="mb-1
            @if($stats['compliance_percentage'] >= 90) text-success
            @elseif($stats['compliance_percentage'] >= 70) text-warning
            @else text-danger
            @endif">
            {{ $stats['compliance_percentage'] }}%
          </h2>
          <p class="text-muted mb-3">Overall Compliance Rate</p>
          <div class="progress mx-auto" style="height: 12px; max-width: 400px;">
            <div class="progress-bar
              @if($stats['compliance_percentage'] >= 90) bg-success
              @elseif($stats['compliance_percentage'] >= 70) bg-warning
              @else bg-danger
              @endif"
              role="progressbar"
              style="width: {{ $stats['compliance_percentage'] }}%;"
              aria-valuenow="{{ $stats['compliance_percentage'] }}"
              aria-valuemin="0"
              aria-valuemax="100">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Department-wise Statistics -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="icon-base ti tabler-building-community me-1"></i>
          Department-wise Statistics
        </h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Department</th>
                <th class="text-center">Total</th>
                <th class="text-center">Complied</th>
                <th class="text-center">Not Complied</th>
                <th class="text-center">N/A</th>
                <th class="text-center">Compliance %</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($departmentStats as $deptStat)
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                          <i class="icon-base ti tabler-building"></i>
                        </span>
                      </div>
                      <div>
                        <span class="fw-semibold">{{ $deptStat['department']->name }}</span>
                        <small class="text-muted d-block">{{ $deptStat['department']->code }}</small>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">{{ $deptStat['total'] }}</td>
                  <td class="text-center text-success fw-semibold">{{ $deptStat['complied'] }}</td>
                  <td class="text-center text-danger fw-semibold">{{ $deptStat['not_complied'] }}</td>
                  <td class="text-center">{{ $deptStat['not_applicable'] }}</td>
                  <td class="text-center">
                    <span class="badge
                      @if($deptStat['compliance_percentage'] >= 90) bg-success
                      @elseif($deptStat['compliance_percentage'] >= 70) bg-warning
                      @else bg-danger
                      @endif">
                      {{ $deptStat['compliance_percentage'] }}%
                    </span>
                  </td>
                  <td class="text-center">
                    <a href="{{ route('audit-reports.department', [$auditPlan, $deptStat['department']]) }}"
                      class="btn btn-sm btn-icon btn-label-primary"
                      data-bs-toggle="tooltip"
                      title="View Details">
                      <i class="icon-base ti tabler-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Findings (Non-Compliances) -->
    @if($findings->count() > 0)
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="icon-base ti tabler-alert-triangle me-1"></i>
            Findings & Non-Compliances ({{ $findings->count() }})
          </h5>
        </div>
        <div class="card-body">
          @foreach($findings as $finding)
            <div class="card border border-danger mb-3">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h6 class="mb-1">
                      <span class="badge bg-danger me-2">Finding #{{ $loop->iteration }}</span>
                      {{ $finding->department->name }}
                    </h6>
                    <small class="text-muted">{{ $finding->checklistGroup->code }} - {{ $finding->checklistGroup->title }}</small>
                  </div>
                  <span class="badge bg-label-danger">Not Complied</span>
                </div>

                <div class="mb-3">
                  <h6 class="mb-2">Question:</h6>
                  <p class="mb-1">{{ $finding->auditQuestion->question_text }}</p>
                  @if($finding->auditQuestion->iso_reference)
                    <span class="badge bg-label-info me-1">{{ $finding->auditQuestion->iso_reference }}</span>
                  @endif
                  @if($finding->auditQuestion->quality_procedure_reference)
                    <span class="badge bg-label-secondary">{{ $finding->auditQuestion->quality_procedure_reference }}</span>
                  @endif
                </div>

                @if($finding->comments)
                  <div class="mb-3">
                    <h6 class="mb-2">Auditor Comments:</h6>
                    <div class="alert alert-warning mb-0">
                      <p class="mb-0">{{ $finding->comments }}</p>
                    </div>
                  </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="text-muted">
                      Audited by: <span class="fw-semibold">{{ $finding->auditor->name }}</span>
                    </small>
                  </div>
                  <div>
                    <small class="text-muted">
                      Date: <span class="fw-semibold">{{ $finding->audited_at->format('M d, Y H:i') }}</span>
                    </small>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @else
      <div class="card">
        <div class="card-body text-center py-6">
          <div class="avatar avatar-xl mx-auto mb-3">
            <span class="avatar-initial rounded-circle bg-label-success">
              <i class="icon-base ti tabler-check" style="font-size: 2rem;"></i>
            </span>
          </div>
          <h5 class="mb-1 text-success">No Non-Compliances Found</h5>
          <p class="text-muted mb-0">All audited items are compliant or not applicable.</p>
        </div>
      </div>
    @endif

    <!-- Audit Details -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">
          <i class="icon-base ti tabler-info-circle me-1"></i>
          Audit Details
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <small class="text-muted d-block">Lead Auditor</small>
            <p class="mb-0 fw-semibold">{{ $auditPlan->leadAuditor->name }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Created By</small>
            <p class="mb-0 fw-semibold">{{ $auditPlan->creator->name }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Created Date</small>
            <p class="mb-0 fw-semibold">{{ $auditPlan->created_at->format('M d, Y H:i') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
