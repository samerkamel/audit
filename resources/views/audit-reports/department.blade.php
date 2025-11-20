@extends('layouts/layoutMaster')

@section('title', 'Department Report - ' . $department->name)

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="d-flex align-items-center mb-2">
              <a href="{{ route('audit-reports.show', $auditPlan) }}" class="btn btn-sm btn-icon btn-label-secondary me-2">
                <i class="icon-base ti tabler-arrow-left"></i>
              </a>
              <h4 class="mb-0">{{ $department->name }}</h4>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2">
              <span class="badge bg-label-primary">{{ $department->code }}</span>
              <span class="badge bg-label-info">{{ $auditPlan->title }}</span>
            </div>
            @if($department->description)
              <p class="text-muted mb-0">{{ $department->description }}</p>
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

    <!-- CheckList Groups -->
    @foreach($groupStats as $groupStat)
      <div class="card mb-4">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">{{ $groupStat['group']->code }}</h5>
              <p class="text-muted mb-0">{{ $groupStat['group']->title }}</p>
              @if($groupStat['group']->quality_procedure_reference)
                <span class="badge bg-label-secondary mt-2">{{ $groupStat['group']->quality_procedure_reference }}</span>
              @endif
            </div>
            <div class="text-center">
              <h4 class="mb-0
                @if($groupStat['compliance_percentage'] >= 90) text-success
                @elseif($groupStat['compliance_percentage'] >= 70) text-warning
                @else text-danger
                @endif">
                {{ $groupStat['compliance_percentage'] }}%
              </h4>
              <small class="text-muted">Compliance</small>
            </div>
          </div>
        </div>

        <div class="card-body">
          <!-- Group Statistics -->
          <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
              <div class="border rounded p-3 text-center">
                <small class="text-muted d-block">Total</small>
                <h6 class="mb-0">{{ $groupStat['total'] }}</h6>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="border rounded p-3 text-center">
                <small class="text-muted d-block">Complied</small>
                <h6 class="mb-0 text-success">{{ $groupStat['complied'] }}</h6>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="border rounded p-3 text-center">
                <small class="text-muted d-block">Not Complied</small>
                <h6 class="mb-0 text-danger">{{ $groupStat['not_complied'] }}</h6>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="border rounded p-3 text-center">
                <small class="text-muted d-block">Not Applicable</small>
                <h6 class="mb-0">{{ $groupStat['not_applicable'] }}</h6>
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-4">
            <div class="progress" style="height: 8px;">
              <div class="progress-bar
                @if($groupStat['compliance_percentage'] >= 90) bg-success
                @elseif($groupStat['compliance_percentage'] >= 70) bg-warning
                @else bg-danger
                @endif"
                role="progressbar"
                style="width: {{ $groupStat['compliance_percentage'] }}%;"
                aria-valuenow="{{ $groupStat['compliance_percentage'] }}"
                aria-valuemin="0"
                aria-valuemax="100">
              </div>
            </div>
          </div>

          <!-- Responses -->
          <h6 class="mb-3">Detailed Responses:</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 45%;">Question</th>
                  <th style="width: 15%;" class="text-center">Response</th>
                  <th style="width: 20%;">Auditor</th>
                  <th style="width: 15%;">Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($groupStat['responses'] as $response)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <p class="mb-1">{{ $response->auditQuestion->question_text }}</p>
                      <div>
                        @if($response->auditQuestion->iso_reference)
                          <span class="badge bg-label-info badge-sm me-1">{{ $response->auditQuestion->iso_reference }}</span>
                        @endif
                        @if($response->auditQuestion->quality_procedure_reference)
                          <span class="badge bg-label-secondary badge-sm">{{ $response->auditQuestion->quality_procedure_reference }}</span>
                        @endif
                      </div>
                      @if($response->comments)
                        <div class="alert alert-{{ $response->response === 'not_complied' ? 'danger' : 'info' }} alert-sm mt-2 mb-0">
                          <small><strong>Comments:</strong> {{ $response->comments }}</small>
                        </div>
                      @endif
                    </td>
                    <td class="text-center">
                      <span class="badge bg-{{ $response->response_color }}">
                        @if($response->response === 'complied')
                          <i class="icon-base ti tabler-check"></i> Complied
                        @elseif($response->response === 'not_complied')
                          <i class="icon-base ti tabler-x"></i> Not Complied
                        @else
                          <i class="icon-base ti tabler-minus"></i> N/A
                        @endif
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs me-2">
                          <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ substr($response->auditor->name, 0, 1) }}
                          </span>
                        </div>
                        <span>{{ $response->auditor->name }}</span>
                      </div>
                    </td>
                    <td>
                      <small>{{ $response->audited_at->format('M d, Y') }}</small>
                      <small class="text-muted d-block">{{ $response->audited_at->format('H:i') }}</small>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      No responses recorded for this checklist group yet.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endforeach

    @if(count($groupStats) == 0)
      <div class="card">
        <div class="card-body text-center py-6">
          <div class="avatar avatar-xl mx-auto mb-3">
            <span class="avatar-initial rounded-circle bg-label-warning">
              <i class="icon-base ti tabler-alert-circle" style="font-size: 2rem;"></i>
            </span>
          </div>
          <h5 class="mb-1">No CheckList Groups</h5>
          <p class="text-muted mb-0">This department doesn't have any checklist groups assigned yet.</p>
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
            <small class="text-muted d-block">Audit Plan</small>
            <p class="mb-0 fw-semibold">{{ $auditPlan->title }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Lead Auditor</small>
            <p class="mb-0 fw-semibold">{{ $auditPlan->leadAuditor->name }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted d-block">Audit Status</small>
            <p class="mb-0">
              <span class="badge bg-label-{{ $auditPlan->status_color }}">
                {{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
