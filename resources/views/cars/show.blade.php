@extends('layouts/layoutMaster')

@section('title', 'CAR Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- CAR Header -->
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">
        CAR: {{ $car->car_number }}
        <span class="badge bg-label-{{ $car->status_color }} ms-2">
          {{ ucwords(str_replace('_', ' ', $car->status)) }}
        </span>
        <span class="badge bg-label-{{ $car->priority_color }} ms-2">
          {{ ucfirst($car->priority) }} Priority
        </span>
      </h4>
      <p class="text-muted mb-0">{{ $car->subject }}</p>
    </div>
    <div class="d-flex gap-2">
      @if(in_array($car->status, ['draft', 'rejected_to_be_edited']))
      <a href="{{ route('cars.edit', $car) }}" class="btn btn-label-primary">
        <i class="icon-base ti tabler-edit me-1"></i> Edit
      </a>
      @endif
      <a href="{{ route('cars.index') }}" class="btn btn-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
      </a>
    </div>
  </div>

  <!-- Workflow Actions -->
  @if($car->status === 'draft')
  <div class="card mb-6">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h6 class="mb-1">Draft CAR - Submit for Approval</h6>
          <p class="text-muted mb-0">This CAR is in draft status. Submit it for quality team approval when ready.</p>
        </div>
        <form action="{{ route('cars.submit-for-approval', $car) }}" method="POST" class="d-inline" onsubmit="return confirm('Submit this CAR for approval?')">
          @csrf
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-send me-1"></i> Submit for Approval
          </button>
        </form>
      </div>
    </div>
  </div>
  @endif

  @if($car->status === 'pending_approval')
  <div class="card mb-6">
    <div class="card-body">
      <h6 class="mb-4">CAR Approval</h6>
      <p class="text-muted mb-4">This CAR is pending quality team approval. Review and approve or reject with clarifications.</p>

      <div class="row g-4">
        <div class="col-md-6">
          <form action="{{ route('cars.approve', $car) }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="form-label" for="clarification_approve">Quality Team Clarification (Optional)</label>
              <textarea class="form-control"
                        id="clarification_approve"
                        name="clarification"
                        rows="3"
                        placeholder="Any additional instructions or clarifications...">{{ old('clarification') }}</textarea>
            </div>
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve and issue this CAR to the responsible department?')">
              <i class="icon-base ti tabler-check me-1"></i> Approve & Issue CAR
            </button>
          </form>
        </div>
        <div class="col-md-6">
          <form action="{{ route('cars.reject', $car) }}" method="POST">
            @csrf
            <div class="mb-4">
              <label class="form-label" for="clarification_reject">Rejection Reason <span class="text-danger">*</span></label>
              <textarea class="form-control"
                        id="clarification_reject"
                        name="clarification"
                        rows="3"
                        placeholder="Explain why this CAR is being rejected..."
                        required>{{ old('clarification') }}</textarea>
            </div>
            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this CAR for editing?')">
              <i class="icon-base ti tabler-x me-1"></i> Reject for Editing
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif

  <div class="row">
    <!-- CAR Details -->
    <div class="col-lg-8">
      <!-- Basic Information -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">CAR Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">CAR Number</label>
              <p class="mb-0">{{ $car->car_number }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">Issued Date</label>
              <p class="mb-0">{{ $car->issued_date->format('M d, Y') }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">Source Type</label>
              <p class="mb-0">
                <span class="badge bg-label-secondary">
                  {{ ucwords(str_replace('_', ' ', $car->source_type)) }}
                </span>
              </p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">Priority</label>
              <p class="mb-0">
                <span class="badge bg-label-{{ $car->priority_color }}">
                  {{ ucfirst($car->priority) }}
                </span>
              </p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">Issued By</label>
              <p class="mb-0">{{ $car->fromDepartment->name }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold text-muted small mb-1">Responsible Department</label>
              <p class="mb-0">{{ $car->toDepartment->name }}</p>
            </div>
            @if($car->auditFinding)
            <div class="col-12">
              <label class="form-label fw-semibold text-muted small mb-1">Related Audit Finding</label>
              <div class="alert alert-info mb-0" role="alert">
                <strong>{{ $car->auditFinding->audit->auditPlan->plan_name }}</strong><br>
                <small>{{ $car->auditFinding->question_text }}</small>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Non-Conformance Description -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Non-Conformance Description</h5>
        </div>
        <div class="card-body">
          <p class="mb-0" style="white-space: pre-wrap;">{{ $car->ncr_description }}</p>
        </div>
      </div>

      <!-- Quality Team Clarification -->
      @if($car->clarification)
      <div class="card mb-6">
        <div class="card-header bg-label-warning">
          <h5 class="card-title mb-0">
            <i class="icon-base ti tabler-info-circle me-2"></i>
            Quality Team Clarification
          </h5>
        </div>
        <div class="card-body">
          <p class="mb-0" style="white-space: pre-wrap;">{{ $car->clarification }}</p>
        </div>
      </div>
      @endif

      <!-- Responses Section -->
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Department Responses</h5>
          @if($car->status === 'issued' || $car->status === 'in_progress')
          <button class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-plus me-1"></i> Add Response
          </button>
          @endif
        </div>
        <div class="card-body">
          @forelse($car->responses as $response)
          <div class="border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">Response from {{ $response->respondedBy->name }}</h6>
                <small class="text-muted">{{ $response->responded_at ? $response->responded_at->format('M d, Y H:i') : 'Not submitted yet' }}</small>
              </div>
              <span class="badge bg-label-{{ $response->response_status_color }}">
                {{ ucfirst($response->response_status) }}
              </span>
            </div>

            <div class="row g-4">
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-1">Root Cause Analysis</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $response->root_cause }}</p>
              </div>
              <div class="col-md-6">
                <label class="fw-semibold text-muted small mb-1">Correction (Short-term)</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $response->correction }}</p>
                <div class="mt-2">
                  <small class="text-muted">Target: {{ $response->correction_target_date->format('M d, Y') }}</small><br>
                  @if($response->correction_actual_date)
                  <small class="text-success">
                    <i class="icon-base ti tabler-check"></i>
                    Completed: {{ $response->correction_actual_date->format('M d, Y') }}
                  </small>
                  @elseif($response->isCorrectionOverdue())
                  <small class="text-danger">
                    <i class="icon-base ti tabler-alert-circle"></i>
                    Overdue
                  </small>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <label class="fw-semibold text-muted small mb-1">Corrective Action (Long-term)</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $response->corrective_action }}</p>
                <div class="mt-2">
                  <small class="text-muted">Target: {{ $response->corrective_action_target_date->format('M d, Y') }}</small><br>
                  @if($response->corrective_action_actual_date)
                  <small class="text-success">
                    <i class="icon-base ti tabler-check"></i>
                    Completed: {{ $response->corrective_action_actual_date->format('M d, Y') }}
                  </small>
                  @elseif($response->isCorrectiveActionOverdue())
                  <small class="text-danger">
                    <i class="icon-base ti tabler-alert-circle"></i>
                    Overdue
                  </small>
                  @endif
                </div>
              </div>
              @if($response->rejection_reason)
              <div class="col-12">
                <div class="alert alert-danger mb-0" role="alert">
                  <strong>Rejection Reason:</strong><br>
                  {{ $response->rejection_reason }}
                </div>
              </div>
              @endif
            </div>
          </div>
          @empty
          <div class="text-center py-6">
            <i class="icon-base ti tabler-inbox-off icon-48px text-muted mb-3"></i>
            <p class="text-muted mb-0">No responses yet</p>
            @if($car->status === 'issued' || $car->status === 'in_progress')
            <small class="text-muted">The responsible department has not submitted a response yet.</small>
            @endif
          </div>
          @endforelse
        </div>
      </div>

      <!-- Follow-ups Section -->
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Follow-ups & Effectiveness Reviews</h5>
          @if($car->status === 'in_progress' || $car->status === 'pending_review')
          <button class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-plus me-1"></i> Add Follow-up
          </button>
          @endif
        </div>
        <div class="card-body">
          @forelse($car->followUps as $followUp)
          <div class="border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">{{ $followUp->follow_up_type_label }}</h6>
                <small class="text-muted">By {{ $followUp->followedUpBy->name }} on {{ $followUp->followed_up_at->format('M d, Y H:i') }}</small>
              </div>
              <span class="badge bg-label-{{ $followUp->follow_up_status_color }}">
                {{ ucfirst($followUp->follow_up_status) }}
              </span>
            </div>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $followUp->follow_up_notes }}</p>
          </div>
          @empty
          <div class="text-center py-6">
            <i class="icon-base ti tabler-clipboard-check icon-48px text-muted mb-3"></i>
            <p class="text-muted mb-0">No follow-ups yet</p>
            @if($car->status === 'in_progress' || $car->status === 'pending_review')
            <small class="text-muted">Quality team has not conducted effectiveness reviews yet.</small>
            @endif
          </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Timeline/Audit Trail -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Activity Timeline</h5>
        </div>
        <div class="card-body">
          <ul class="timeline mb-0">
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-primary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">CAR Created</h6>
                  <small class="text-muted">{{ $car->created_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 text-muted">Issued by {{ $car->issuedBy->name }}</p>
              </div>
            </li>
            @if($car->approved_by)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-success"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">CAR Approved</h6>
                  <small class="text-muted">{{ $car->approved_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 text-muted">By {{ $car->approvedBy->name }}</p>
              </div>
            </li>
            @endif
            @foreach($car->responses as $response)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-info"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Response Submitted</h6>
                  @if($response->responded_at)
                  <small class="text-muted">{{ $response->responded_at->format('M d, Y H:i') }}</small>
                  @endif
                </div>
                <p class="mb-0 text-muted">By {{ $response->respondedBy->name }}</p>
              </div>
            </li>
            @endforeach
            @foreach($car->followUps as $followUp)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-warning"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Follow-up Conducted</h6>
                  <small class="text-muted">{{ $followUp->followed_up_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 text-muted">{{ $followUp->follow_up_type_label }} - {{ ucfirst($followUp->follow_up_status) }}</p>
              </div>
            </li>
            @endforeach
          </ul>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Quick Information</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Total Responses</span>
            <span class="badge bg-label-primary">{{ $car->responses->count() }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Follow-ups</span>
            <span class="badge bg-label-info">{{ $car->followUps->count() }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Created</span>
            <span class="text-muted">{{ $car->created_at->diffForHumans() }}</span>
          </div>
          @if($car->latestResponse)
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">Latest Response</span>
            <span class="text-muted">{{ $car->latestResponse->updated_at->diffForHumans() }}</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
