@extends('layouts/layoutMaster')

@section('title', 'IO Details - ' . $improvementOpportunity->io_number)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">{{ $improvementOpportunity->io_number }}</h4>
      <p class="text-muted mb-0">{{ $improvementOpportunity->subject }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('improvement-opportunities.index') }}" class="btn btn-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
      </a>
      @if(in_array($improvementOpportunity->status, ['draft', 'rejected_to_be_edited']))
      <a href="{{ route('improvement-opportunities.edit', $improvementOpportunity) }}" class="btn btn-primary">
        <i class="icon-base ti tabler-edit me-1"></i> Edit
      </a>
      @endif
    </div>
  </div>

  <div class="row">
    <!-- Main Details Card -->
    <div class="col-lg-8">
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Improvement Opportunity Details</h5>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <p class="mb-1 text-muted">IO Number</p>
              <h6>{{ $improvementOpportunity->io_number }}</h6>
            </div>
            <div class="col-md-6">
              <p class="mb-1 text-muted">Status</p>
              <span class="badge bg-label-{{ $improvementOpportunity->status_color }}">
                {{ ucwords(str_replace('_', ' ', $improvementOpportunity->status)) }}
              </span>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <p class="mb-1 text-muted">Source Type</p>
              <h6>{{ ucwords(str_replace('_', ' ', $improvementOpportunity->source_type)) }}</h6>
            </div>
            <div class="col-md-6">
              <p class="mb-1 text-muted">Priority</p>
              <span class="badge bg-label-{{ $improvementOpportunity->priority_color }}">
                {{ ucfirst($improvementOpportunity->priority) }}
              </span>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <p class="mb-1 text-muted">Issued By (Department)</p>
              <h6>{{ $improvementOpportunity->fromDepartment->name }}</h6>
            </div>
            <div class="col-md-6">
              <p class="mb-1 text-muted">Responsible Department</p>
              <h6>{{ $improvementOpportunity->toDepartment->name }}</h6>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6">
              <p class="mb-1 text-muted">Issued Date</p>
              <h6>{{ $improvementOpportunity->issued_date->format('M d, Y') }}</h6>
            </div>
            <div class="col-md-6">
              <p class="mb-1 text-muted">Issued By (User)</p>
              <h6>{{ $improvementOpportunity->issuedBy->name ?? 'N/A' }}</h6>
            </div>
          </div>

          <hr class="my-4">

          <div class="mb-4">
            <p class="mb-1 text-muted">Subject</p>
            <h6>{{ $improvementOpportunity->subject }}</h6>
          </div>

          <div class="mb-4">
            <p class="mb-1 text-muted">Observation Description</p>
            <p class="mb-0">{{ $improvementOpportunity->observation_description }}</p>
          </div>

          @if($improvementOpportunity->improvement_suggestion)
          <div class="mb-4">
            <p class="mb-1 text-muted">Improvement Suggestion</p>
            <p class="mb-0">{{ $improvementOpportunity->improvement_suggestion }}</p>
          </div>
          @endif

          @if($improvementOpportunity->clarification)
          <div class="mb-4">
            <p class="mb-1 text-muted">Quality Team Clarification</p>
            <p class="mb-0">{{ $improvementOpportunity->clarification }}</p>
          </div>
          @endif

          @if($improvementOpportunity->auditFinding)
          <hr class="my-4">
          <div class="mb-4">
            <p class="mb-1 text-muted">Related Audit Finding</p>
            <div class="alert alert-info mb-0">
              <strong>Audit:</strong> {{ $improvementOpportunity->auditFinding->audit->auditPlan->plan_name ?? 'N/A' }}<br>
              <strong>Question:</strong> {{ $improvementOpportunity->auditFinding->question_text ?? 'N/A' }}<br>
              <strong>Remarks:</strong> {{ $improvementOpportunity->auditFinding->auditor_remarks ?? 'N/A' }}
            </div>
          </div>
          @endif
        </div>
      </div>

      <!-- Responses Section -->
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Responses</h5>
          @if(in_array($improvementOpportunity->status, ['issued', 'in_progress', 'pending_review']))
          <a href="{{ route('improvement-opportunity-responses.create', $improvementOpportunity) }}" class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-plus me-1"></i> Add Response
          </a>
          @endif
        </div>
        <div class="card-body">
          @if($improvementOpportunity->responses->isEmpty())
          <div class="text-center py-4">
            <i class="icon-base ti tabler-inbox-off icon-48px text-muted mb-2"></i>
            <p class="text-muted mb-0">No responses yet</p>
          </div>
          @else
          @foreach($improvementOpportunity->responses as $response)
          <div class="border rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h6 class="mb-1">Response #{{ $loop->iteration }}</h6>
                <small class="text-muted">
                  By {{ $response->respondedBy->name ?? 'N/A' }} on {{ $response->created_at->format('M d, Y H:i') }}
                </small>
              </div>
              <span class="badge bg-label-{{ $response->response_status_color }}">
                {{ ucfirst($response->response_status) }}
              </span>
            </div>

            <div class="mb-3">
              <p class="mb-1 text-muted small">Proposed Action</p>
              <p class="mb-0">{{ $response->proposed_action }}</p>
            </div>

            @if($response->implementation_plan)
            <div class="mb-3">
              <p class="mb-1 text-muted small">Implementation Plan</p>
              <p class="mb-0">{{ $response->implementation_plan }}</p>
            </div>
            @endif

            <div class="row mb-3">
              <div class="col-md-6">
                <p class="mb-1 text-muted small">Target Date</p>
                <p class="mb-0">{{ $response->target_date->format('M d, Y') }}</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1 text-muted small">Actual Date</p>
                <p class="mb-0">{{ $response->actual_date ? $response->actual_date->format('M d, Y') : 'Not completed' }}</p>
              </div>
            </div>

            @if($response->outcome)
            <div class="mb-3">
              <p class="mb-1 text-muted small">Outcome</p>
              <p class="mb-0">{{ $response->outcome }}</p>
            </div>
            @endif

            @if($response->rejection_reason)
            <div class="alert alert-danger mb-0">
              <strong>Rejection Reason:</strong> {{ $response->rejection_reason }}
            </div>
            @endif
          </div>
          @endforeach
          @endif
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Actions Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Actions</h5>
        </div>
        <div class="card-body">
          @if($improvementOpportunity->status === 'draft')
          <form action="{{ route('improvement-opportunities.submit-for-approval', $improvementOpportunity) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
              <i class="icon-base ti tabler-send me-1"></i> Submit for Approval
            </button>
          </form>
          @endif

          @if($improvementOpportunity->status === 'pending_approval')
          <form action="{{ route('improvement-opportunities.approve', $improvementOpportunity) }}" method="POST" class="mb-3">
            @csrf
            <div class="mb-3">
              <label class="form-label" for="approval_clarification">Clarification (Optional)</label>
              <textarea class="form-control" id="approval_clarification" name="clarification" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">
              <i class="icon-base ti tabler-check me-1"></i> Approve
            </button>
          </form>

          <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
            <i class="icon-base ti tabler-x me-1"></i> Reject
          </button>
          @endif

          @if(in_array($improvementOpportunity->status, ['in_progress', 'pending_review']))
          @php
            $hasAcceptedResponse = $improvementOpportunity->responses->where('response_status', 'accepted')->isNotEmpty();
          @endphp
          @if($hasAcceptedResponse)
          <form action="{{ route('improvement-opportunities.close', $improvementOpportunity) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to close this IO?')">
              <i class="icon-base ti tabler-circle-check me-1"></i> Close IO
            </button>
          </form>
          @else
          <button type="button" class="btn btn-secondary w-100" disabled>
            <i class="icon-base ti tabler-circle-check me-1"></i> Close IO (Requires Accepted Response)
          </button>
          @endif
          @endif

          @if($improvementOpportunity->status === 'closed')
          <div class="alert alert-success mb-0">
            <i class="icon-base ti tabler-circle-check me-2"></i>
            <strong>This IO has been closed</strong>
            @if($improvementOpportunity->closedBy)
            <br><small>Closed by {{ $improvementOpportunity->closedBy->name }} on {{ $improvementOpportunity->closed_at->format('M d, Y') }}</small>
            @endif
          </div>
          @endif
        </div>
      </div>

      <!-- Timeline Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Timeline</h5>
        </div>
        <div class="card-body">
          <ul class="timeline mb-0">
            <li class="timeline-item">
              <span class="timeline-indicator timeline-indicator-primary">
                <i class="ti tabler-file-plus"></i>
              </span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Created</h6>
                  <small class="text-muted">{{ $improvementOpportunity->created_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 small">By {{ $improvementOpportunity->issuedBy->name ?? 'N/A' }}</p>
              </div>
            </li>

            @if($improvementOpportunity->approved_at)
            <li class="timeline-item">
              <span class="timeline-indicator timeline-indicator-success">
                <i class="ti tabler-check"></i>
              </span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Approved</h6>
                  <small class="text-muted">{{ $improvementOpportunity->approved_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 small">By {{ $improvementOpportunity->approvedBy->name ?? 'N/A' }}</p>
              </div>
            </li>
            @endif

            @if($improvementOpportunity->closed_at)
            <li class="timeline-item">
              <span class="timeline-indicator timeline-indicator-success">
                <i class="ti tabler-circle-check"></i>
              </span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <h6 class="mb-0">Closed</h6>
                  <small class="text-muted">{{ $improvementOpportunity->closed_at->format('M d, Y H:i') }}</small>
                </div>
                <p class="mb-0 small">By {{ $improvementOpportunity->closedBy->name ?? 'N/A' }}</p>
              </div>
            </li>
            @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('improvement-opportunities.reject', $improvementOpportunity) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Reject Improvement Opportunity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="rejection_clarification">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="rejection_clarification" name="clarification" rows="4" required></textarea>
            <small class="form-text text-muted">Please provide detailed feedback for the issuer.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
