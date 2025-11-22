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

  @php
    $hasAcceptedResponse = $car->responses()->where('response_status', 'accepted')->exists();
    $hasFollowUps = $car->followUps()->count() > 0;
    $allFollowUpsAccepted = $car->followUps()->where('follow_up_status', '!=', 'accepted')->doesntExist();
    $canClose = $hasAcceptedResponse && $hasFollowUps && $allFollowUpsAccepted && $car->status !== 'closed';
  @endphp

  @if($canClose)
  <div class="card mb-6">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h6 class="mb-1 text-success">
            <i class="icon-base ti tabler-circle-check me-2"></i>Ready for Closure
          </h6>
          <p class="text-muted mb-0">All effectiveness reviews are accepted. This CAR can now be closed.</p>
        </div>
        <form action="{{ route('cars.close', $car) }}" method="POST" class="d-inline" onsubmit="return confirm('Close this CAR? This action marks the corrective action as complete and effective.')">
          @csrf
          <button type="submit" class="btn btn-success">
            <i class="icon-base ti tabler-lock me-1"></i> Close CAR
          </button>
        </form>
      </div>
    </div>
  </div>
  @endif

  @if($car->status === 'closed')
  <div class="card mb-6">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h6 class="mb-1 text-success">
            <i class="icon-base ti tabler-check me-2"></i>CAR Closed
          </h6>
          <p class="text-muted mb-0">
            Closed by {{ $car->closedBy->name }} on {{ $car->closed_at->format('F d, Y H:i') }}
          </p>
        </div>
        <span class="badge bg-success">Completed</span>
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
          @if(in_array($car->status, ['issued', 'in_progress']) && auth()->user()?->department_id === $car->to_department_id)
          <a href="{{ route('cars.responses.create', $car) }}" class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-plus me-1"></i> Add Response
          </a>
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
              <div class="d-flex gap-2">
                <span class="badge bg-label-{{ $response->response_status_color }}">
                  {{ ucwords(str_replace('_', ' ', $response->response_status)) }}
                </span>
                @if(in_array($response->response_status, ['pending', 'rejected']) && auth()->user()?->department_id === $car->to_department_id)
                <a href="{{ route('cars.responses.edit', [$car, $response]) }}" class="btn btn-xs btn-label-primary">
                  <i class="icon-base ti tabler-edit"></i>
                </a>
                @endif
              </div>
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

              @if($response->attachments && count($response->attachments) > 0)
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-2">Attachments</label>
                <div class="list-group">
                  @foreach($response->attachments as $attachment)
                  <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="icon-base ti tabler-file me-2"></i>
                    <span>{{ $attachment['name'] }}</span>
                    <small class="text-muted ms-auto">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                  </a>
                  @endforeach
                </div>
              </div>
              @endif

              @if($response->rejection_reason)
              <div class="col-12">
                <div class="alert alert-danger mb-0" role="alert">
                  <strong>Rejection Reason:</strong><br>
                  {{ $response->rejection_reason }}
                </div>
              </div>
              @endif

              @if($response->response_status === 'submitted')
              <div class="col-12">
                <div class="d-flex gap-2">
                  <form action="{{ route('cars.responses.accept', [$car, $response]) }}" method="POST" class="flex-fill" onsubmit="return confirm('Accept this response?')">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                      <i class="icon-base ti tabler-check me-1"></i> Accept Response
                    </button>
                  </form>
                  <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $response->id }}">
                    <i class="icon-base ti tabler-x me-1"></i> Reject Response
                  </button>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal{{ $response->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <form action="{{ route('cars.responses.reject', [$car, $response]) }}" method="POST">
                      @csrf
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Reject Response</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <label class="form-label" for="rejection_reason{{ $response->id }}">Rejection Reason <span class="text-danger">*</span></label>
                          <textarea class="form-control" id="rejection_reason{{ $response->id }}" name="rejection_reason" rows="4" placeholder="Explain why this response is being rejected..." required></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-danger">Reject Response</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
          @empty
          <div class="text-center py-6">
            <i class="icon-base ti tabler-inbox-off icon-48px text-muted mb-3"></i>
            <p class="text-muted mb-0">No responses yet</p>
            @if(in_array($car->status, ['issued', 'in_progress']))
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
          @if($car->responses()->where('response_status', 'accepted')->exists() && $car->status !== 'closed')
          <a href="{{ route('cars.follow-ups.create', $car) }}" class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-plus me-1"></i> Add Follow-up
          </a>
          @endif
        </div>
        <div class="card-body">
          @forelse($car->followUps as $followUp)
          <div class="border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">Follow-up conducted by {{ $followUp->followedUpBy->name }}</h6>
                <small class="text-muted">{{ $followUp->follow_up_date->format('F d, Y') }} | Reviewed: {{ $followUp->reviewed_at ? $followUp->reviewed_at->format('M d, Y H:i') : 'Pending' }}</small>
              </div>
              <div class="d-flex gap-2">
                <span class="badge bg-label-{{ $followUp->status_color }}">
                  {{ ucwords(str_replace('_', ' ', $followUp->status)) }}
                </span>
                @if(in_array($followUp->status, ['pending', 'not_accepted']))
                <a href="{{ route('cars.follow-ups.edit', [$car, $followUp]) }}" class="btn btn-xs btn-label-primary">
                  <i class="icon-base ti tabler-edit"></i>
                </a>
                @endif
              </div>
            </div>

            <div class="row g-4">
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-1">Effectiveness Assessment</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $followUp->effectiveness_review }}</p>
              </div>

              @if($followUp->verification_evidence)
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-1">Verification Evidence</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $followUp->verification_evidence }}</p>
              </div>
              @endif

              @if($followUp->remarks)
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-1">Additional Remarks</label>
                <p class="mb-0" style="white-space: pre-wrap;">{{ $followUp->remarks }}</p>
              </div>
              @endif

              @if($followUp->attachments && count($followUp->attachments) > 0)
              <div class="col-12">
                <label class="fw-semibold text-muted small mb-2">Attachments</label>
                <div class="list-group">
                  @foreach($followUp->attachments as $attachment)
                  <a href="{{ Storage::url($attachment['path']) }}" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                    <i class="icon-base ti tabler-file me-2"></i>
                    <span>{{ $attachment['name'] }}</span>
                    <small class="text-muted ms-auto">({{ number_format($attachment['size'] / 1024, 2) }} KB)</small>
                  </a>
                  @endforeach
                </div>
              </div>
              @endif

              @if($followUp->rejection_reason)
              <div class="col-12">
                <div class="alert alert-danger mb-0" role="alert">
                  <strong>Not Effective - Reason:</strong><br>
                  {{ $followUp->rejection_reason }}
                </div>
              </div>
              @endif

              @if($followUp->status === 'pending')
              <div class="col-12">
                <div class="d-flex gap-2">
                  <form action="{{ route('cars.follow-ups.accept', [$car, $followUp]) }}" method="POST" class="flex-fill" onsubmit="return confirm('Mark this follow-up as effective?')">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                      <i class="icon-base ti tabler-check me-1"></i> Mark as Effective
                    </button>
                  </form>
                  <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#rejectFollowUpModal{{ $followUp->id }}">
                    <i class="icon-base ti tabler-x me-1"></i> Not Effective
                  </button>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectFollowUpModal{{ $followUp->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <form action="{{ route('cars.follow-ups.reject', [$car, $followUp]) }}" method="POST">
                      @csrf
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Mark as Not Effective</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <label class="form-label" for="rejection_reason{{ $followUp->id }}">Reason <span class="text-danger">*</span></label>
                          <textarea class="form-control" id="rejection_reason{{ $followUp->id }}" name="rejection_reason" rows="4" placeholder="Explain why the corrective actions are not effective..." required></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-danger">Mark as Not Effective</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endif

              @if($followUp->status === 'pending')
              <div class="col-12">
                <form action="{{ route('cars.follow-ups.destroy', [$car, $followUp]) }}" method="POST" onsubmit="return confirm('Delete this follow-up?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-label-danger">
                    <i class="icon-base ti tabler-trash me-1"></i> Delete Follow-up
                  </button>
                </form>
              </div>
              @endif
            </div>
          </div>
          @empty
          <div class="text-center py-6">
            <i class="icon-base ti tabler-clipboard-check icon-48px text-muted mb-3"></i>
            <p class="text-muted mb-0">No follow-ups yet</p>
            @if($car->responses()->where('response_status', 'accepted')->exists())
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
