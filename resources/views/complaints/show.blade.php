@extends('layouts/layoutMaster')

@section('title', __('Complaint Details'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Complaint {{ $complaint->complaint_number }}</h4>
      <p class="text-muted mb-0">{{ $complaint->complaint_subject }}</p>
    </div>
    <div class="d-flex gap-2">
      @if(in_array($complaint->status, ['new', 'acknowledged']))
      <a href="{{ route('complaints.edit', $complaint) }}" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-edit me-1"></i> {{ __('Edit') }}
      </a>
      @endif
      <a href="{{ route('complaints.index') }}" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back to List') }}
      </a>
    </div>
  </div>

  <!-- Status Alert -->
  @if($complaint->status === 'closed')
  <div class="alert alert-success alert-dismissible" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti tabler-circle-check me-2"></i> {{ __('Complaint Closed') }}
    </h5>
    <p class="mb-0">
      {{ __('This complaint was successfully resolved and closed on') }} {{ $complaint->closed_at->format('F d, Y') }}
      @if($complaint->closedBy)
      {{ __('by') }} {{ $complaint->closedBy->name }}
      @endif
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @elseif($complaint->isOverdue())
  <div class="alert alert-danger alert-dismissible" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti tabler-alert-triangle me-2"></i> {{ __('Overdue Response') }}
    </h5>
    <p class="mb-0">
      {{ __('This complaint is overdue. Expected response date was') }} {{ $complaint->response_date->format('F d, Y') }}.
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
      <!-- Complaint Information -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Complaint Information') }}</h5>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Complaint Number') }}</label>
              <p class="mb-0">{{ $complaint->complaint_number }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Complaint Date') }}</label>
              <p class="mb-0">{{ $complaint->complaint_date->format('F d, Y') }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Expected Response Date') }}</label>
              <p class="mb-0">
                @if($complaint->response_date)
                {{ $complaint->response_date->format('F d, Y') }}
                @if($complaint->isOverdue())
                <span class="badge bg-label-danger ms-2">{{ __('Overdue') }}</span>
                @endif
                @else
                <span class="text-muted">{{ __('Not set') }}</span>
                @endif
              </p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Received By') }}</label>
              <p class="mb-0">
                @if($complaint->receivedBy)
                {{ $complaint->receivedBy->name }}
                @else
                <span class="text-muted">{{ __('Unknown') }}</span>
                @endif
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Customer Information -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Customer Information') }}</h5>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Customer Name') }}</label>
              <p class="mb-0">{{ $complaint->customer_name }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Company') }}</label>
              <p class="mb-0">{{ $complaint->customer_company ?? '—' }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Email') }}</label>
              <p class="mb-0">
                @if($complaint->customer_email)
                <a href="mailto:{{ $complaint->customer_email }}">{{ $complaint->customer_email }}</a>
                @else
                —
                @endif
              </p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Phone') }}</label>
              <p class="mb-0">
                @if($complaint->customer_phone)
                <a href="tel:{{ $complaint->customer_phone }}">{{ $complaint->customer_phone }}</a>
                @else
                —
                @endif
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Complaint Details -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Complaint Details') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Subject') }}</label>
            <p class="mb-0">{{ $complaint->complaint_subject }}</p>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Description') }}</label>
            <div class="alert alert-warning mb-0" role="alert">
              {{ $complaint->complaint_description }}
            </div>
          </div>
          <div class="row g-4">
            <div class="col-md-4">
              <label class="form-label fw-semibold">{{ __('Category') }}</label>
              <p class="mb-0">
                <span class="badge bg-label-secondary">{{ $complaint->category_label }}</span>
              </p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">{{ __('Priority') }}</label>
              <p class="mb-0">
                <span class="badge bg-label-{{ $complaint->priority_color }}">{{ ucfirst($complaint->priority) }}</span>
              </p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">{{ __('Severity') }}</label>
              <p class="mb-0">
                <span class="badge bg-label-{{ $complaint->severity_color }}">{{ ucfirst($complaint->severity) }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Initial Response (if acknowledged) -->
      @if($complaint->initial_response)
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Initial Response') }}</h5>
        </div>
        <div class="card-body">
          <p class="mb-0">{{ $complaint->initial_response }}</p>
        </div>
      </div>
      @endif

      <!-- Root Cause Analysis & Resolution (if resolved) -->
      @if($complaint->status === 'resolved' || $complaint->status === 'closed')
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Resolution Details') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Root Cause Analysis') }}</label>
            <p class="mb-0">{{ $complaint->root_cause_analysis }}</p>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Corrective Action') }}</label>
            <p class="mb-0">{{ $complaint->corrective_action }}</p>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Resolution') }}</label>
            <p class="mb-0">{{ $complaint->resolution }}</p>
          </div>
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Resolved Date') }}</label>
              <p class="mb-0">{{ $complaint->resolved_date->format('F d, Y') }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">{{ __('Resolved By') }}</label>
              <p class="mb-0">
                @if($complaint->resolvedBy)
                {{ $complaint->resolvedBy->name }}
                @else
                <span class="text-muted">{{ __('Unknown') }}</span>
                @endif
              </p>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Customer Satisfaction (if closed) -->
      @if($complaint->status === 'closed' && ($complaint->satisfaction_rating || $complaint->customer_feedback))
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Customer Satisfaction') }}</h5>
        </div>
        <div class="card-body">
          @if($complaint->satisfaction_rating)
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Rating') }}</label>
            <p class="mb-0">
              @for($i = 1; $i <= 5; $i++)
              @if($i <= $complaint->satisfaction_rating)
              <i class="icon-base ti tabler-star-filled text-warning"></i>
              @else
              <i class="icon-base ti tabler-star text-muted"></i>
              @endif
              @endfor
              <span class="ms-2">({{ $complaint->satisfaction_rating }}/5)</span>
            </p>
          </div>
          @endif
          @if($complaint->customer_feedback)
          <div>
            <label class="form-label fw-semibold">{{ __('Feedback') }}</label>
            <p class="mb-0">{{ $complaint->customer_feedback }}</p>
          </div>
          @endif
        </div>
      </div>
      @endif
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
      <!-- Status Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Status') }}</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="fw-medium">{{ __('Current Status') }}</span>
            <span class="badge bg-label-{{ $complaint->status_color }}">
              {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
            </span>
          </div>

          <!-- Workflow Actions -->
          @if($complaint->status === 'new')
          <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#acknowledgeModal">
            <i class="icon-base ti tabler-check me-1"></i> {{ __('Acknowledge Complaint') }}
          </button>
          @elseif($complaint->status === 'acknowledged')
          <form action="{{ route('complaints.investigate', $complaint) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-warning w-100">
              <i class="icon-base ti tabler-search me-1"></i> {{ __('Start Investigation') }}
            </button>
          </form>
          @elseif($complaint->status === 'investigating')
          <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#resolveModal">
            <i class="icon-base ti tabler-circle-check me-1"></i> {{ __('Resolve Complaint') }}
          </button>
          @elseif($complaint->status === 'resolved' && $complaint->canBeClosed())
          <form action="{{ route('complaints.close', $complaint) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to close this complaint?') }}')">
            @csrf
            <button type="submit" class="btn btn-secondary w-100">
              <i class="icon-base ti tabler-lock me-1"></i> {{ __('Close Complaint') }}
            </button>
          </form>
          @endif
        </div>
      </div>

      <!-- Assignment Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Assignment') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-4">
            <label class="form-label fw-semibold">{{ __('Assigned Department') }}</label>
            <p class="mb-0">
              @if($complaint->assignedToDepartment)
              {{ $complaint->assignedToDepartment->name }}
              @else
              <span class="text-muted">{{ __('Not assigned') }}</span>
              @endif
            </p>
          </div>
          <div>
            <label class="form-label fw-semibold">{{ __('Assigned User') }}</label>
            <p class="mb-0">
              @if($complaint->assignedToUser)
              {{ $complaint->assignedToUser->name }}
              @else
              <span class="text-muted">{{ __('Not assigned') }}</span>
              @endif
            </p>
          </div>
        </div>
      </div>

      <!-- CAR Integration Card -->
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Corrective Action') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">{{ __('CAR Required') }}</label>
            <p class="mb-0">
              @if($complaint->car_required)
              <span class="badge bg-label-success">{{ __('Yes') }}</span>
              @else
              <span class="badge bg-label-secondary">{{ __('No') }}</span>
              @endif
            </p>
          </div>

          @if($complaint->car)
          <div class="alert alert-info mb-0" role="alert">
            <h6 class="alert-heading mb-2">{{ __('CAR Generated') }}</h6>
            <p class="mb-2">
              <a href="{{ route('cars.show', $complaint->car) }}" class="alert-link">
                {{ $complaint->car->car_number }}
              </a>
            </p>
            <small>{{ $complaint->car->subject }}</small>
          </div>
          @elseif($complaint->canGenerateCar())
          <form action="{{ route('complaints.generate-car', $complaint) }}" method="POST" onsubmit="return confirm('{{ __('Generate CAR from this complaint?') }}')">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
              <i class="icon-base ti tabler-file-arrow-right me-1"></i> {{ __('Generate CAR') }}
            </button>
          </form>
          @endif
        </div>
      </div>

      <!-- Timestamps -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Timeline') }}</h5>
        </div>
        <div class="card-body">
          <ul class="timeline mb-0">
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-primary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">{{ __('Created') }}</h6>
                  <small class="text-muted">{{ $complaint->created_at->format('M d, Y g:i A') }}</small>
                </div>
              </div>
            </li>
            @if($complaint->updated_at != $complaint->created_at)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-info"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">{{ __('Last Updated') }}</h6>
                  <small class="text-muted">{{ $complaint->updated_at->format('M d, Y g:i A') }}</small>
                </div>
              </div>
            </li>
            @endif
            @if($complaint->resolved_date)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-success"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">{{ __('Resolved') }}</h6>
                  <small class="text-muted">{{ $complaint->resolved_date->format('M d, Y') }}</small>
                </div>
              </div>
            </li>
            @endif
            @if($complaint->closed_at)
            <li class="timeline-item timeline-item-transparent pb-0">
              <span class="timeline-point timeline-point-secondary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">{{ __('Closed') }}</h6>
                  <small class="text-muted">{{ $complaint->closed_at->format('M d, Y g:i A') }}</small>
                </div>
              </div>
            </li>
            @endif
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Acknowledge Modal -->
<div class="modal fade" id="acknowledgeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Acknowledge Complaint') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('complaints.acknowledge', $complaint) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="initial_response">{{ __('Initial Response') }} <span class="text-danger">*</span></label>
            <textarea
              class="form-control"
              id="initial_response"
              name="initial_response"
              rows="6"
              placeholder="{{ __('Provide initial acknowledgment and assessment of the complaint...') }}"
              required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-check me-1"></i> {{ __('Acknowledge') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Resolve Complaint') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('complaints.resolve', $complaint) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-4">
            <label class="form-label" for="root_cause_analysis">{{ __('Root Cause Analysis') }} <span class="text-danger">*</span></label>
            <textarea
              class="form-control"
              id="root_cause_analysis"
              name="root_cause_analysis"
              rows="6"
              placeholder="{{ __('Describe the root cause analysis of the complaint...') }}"
              required></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label" for="corrective_action">{{ __('Corrective Action') }} <span class="text-danger">*</span></label>
            <textarea
              class="form-control"
              id="corrective_action"
              name="corrective_action"
              rows="6"
              placeholder="{{ __('Describe the corrective actions taken...') }}"
              required></textarea>
          </div>
          <div class="mb-0">
            <label class="form-label" for="resolution">{{ __('Resolution') }} <span class="text-danger">*</span></label>
            <textarea
              class="form-control"
              id="resolution"
              name="resolution"
              rows="6"
              placeholder="{{ __('Describe how the complaint was resolved...') }}"
              required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-success">
            <i class="icon-base ti tabler-circle-check me-1"></i> {{ __('Resolve') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
