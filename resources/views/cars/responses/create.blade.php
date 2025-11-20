@extends('layouts/layoutMaster')

@section('title', 'Respond to CAR')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/dropzone/dropzone.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/dropzone/dropzone.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Respond to CAR {{ $car->car_number }}</h4>
      <p class="text-muted mb-0">Submit root cause analysis and action plans</p>
    </div>
    <a href="{{ route('cars.show', $car) }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> Back to CAR
    </a>
  </div>

  <!-- CAR Information -->
  <div class="card mb-6">
    <div class="card-header">
      <h5 class="card-title mb-0">CAR Information</h5>
    </div>
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">From Department</label>
          <p class="mb-0">{{ $car->fromDepartment->name }}</p>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">To Department</label>
          <p class="mb-0">{{ $car->toDepartment->name }}</p>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Issued Date</label>
          <p class="mb-0">{{ $car->issued_date->format('F d, Y') }}</p>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Priority</label>
          <p class="mb-0">
            <span class="badge bg-label-{{ $car->priority_color }}">
              {{ ucfirst($car->priority) }}
            </span>
          </p>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Subject</label>
          <p class="mb-0">{{ $car->subject }}</p>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Non-Conformance Description</label>
          <div class="alert alert-warning mb-0" role="alert">
            {{ $car->ncr_description }}
          </div>
        </div>
        @if($car->clarification)
        <div class="col-12">
          <label class="form-label fw-semibold">Quality Team Clarification</label>
          <div class="alert alert-info mb-0" role="alert">
            {{ $car->clarification }}
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Response Form -->
  <form action="{{ route('cars.responses.store', $car) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Root Cause Analysis -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-search me-2"></i> Root Cause Analysis
        </h5>
      </div>
      <div class="card-body">
        <div class="mb-0">
          <label class="form-label" for="root_cause">Identify the Root Cause <span class="text-danger">*</span></label>
          <textarea
            class="form-control @error('root_cause') is-invalid @enderror"
            id="root_cause"
            name="root_cause"
            rows="6"
            placeholder="Use techniques like 5 Whys, Fishbone Diagram, or other root cause analysis methods to identify the fundamental cause of the non-conformance..."
            required>{{ old('root_cause') }}</textarea>
          @error('root_cause')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Describe the underlying cause(s) of the non-conformance</small>
        </div>
      </div>
    </div>

    <!-- Correction (Short-term Action) -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-bolt me-2"></i> Correction (Short-term Action)
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-12">
            <label class="form-label" for="correction">Immediate Correction <span class="text-danger">*</span></label>
            <textarea
              class="form-control @error('correction') is-invalid @enderror"
              id="correction"
              name="correction"
              rows="5"
              placeholder="Describe the immediate action taken to address the symptom of the non-conformance..."
              required>{{ old('correction') }}</textarea>
            @error('correction')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Short-term action to address the immediate problem</small>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="correction_target_date">Target Completion Date <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control flatpickr-date @error('correction_target_date') is-invalid @enderror"
              id="correction_target_date"
              name="correction_target_date"
              value="{{ old('correction_target_date') }}"
              placeholder="Select target date"
              required />
            @error('correction_target_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Corrective Action (Long-term Action) -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-shield-check me-2"></i> Corrective Action (Long-term Action)
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-12">
            <label class="form-label" for="corrective_action">Preventive Action <span class="text-danger">*</span></label>
            <textarea
              class="form-control @error('corrective_action') is-invalid @enderror"
              id="corrective_action"
              name="corrective_action"
              rows="6"
              placeholder="Describe the systemic changes or improvements to prevent recurrence of the root cause..."
              required>{{ old('corrective_action') }}</textarea>
            @error('corrective_action')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Long-term action to eliminate the root cause and prevent recurrence</small>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="corrective_action_target_date">Target Completion Date <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control flatpickr-date @error('corrective_action_target_date') is-invalid @enderror"
              id="corrective_action_target_date"
              name="corrective_action_target_date"
              value="{{ old('corrective_action_target_date') }}"
              placeholder="Select target date"
              required />
            @error('corrective_action_target_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Supporting Documents -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-paperclip me-2"></i> Supporting Documents
        </h5>
      </div>
      <div class="card-body">
        <div class="mb-0">
          <label class="form-label" for="attachments">Upload Evidence/Attachments</label>
          <input
            type="file"
            class="form-control @error('attachments.*') is-invalid @enderror"
            id="attachments"
            name="attachments[]"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" />
          @error('attachments.*')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">
            Upload supporting documents, photos, or evidence (PDF, Word, Excel, Images). Max 10MB per file.
          </small>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('cars.show', $car) }}" class="btn btn-label-secondary">
            <i class="icon-base ti tabler-x me-1"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-send me-1"></i> Submit Response for Review
          </button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize Flatpickr for date pickers
  $('.flatpickr-date').flatpickr({
    dateFormat: 'Y-m-d',
    minDate: 'today',
    onClose: function(selectedDates, dateStr, instance) {
      // If correction target date is selected, set minimum for corrective action
      if (instance.element.id === 'correction_target_date' && dateStr) {
        const correctiveActionPicker = document.getElementById('corrective_action_target_date')._flatpickr;
        correctiveActionPicker.set('minDate', dateStr);
      }
    }
  });

  // Validation helper
  $('form').on('submit', function(e) {
    const correctionDate = new Date($('#correction_target_date').val());
    const correctiveActionDate = new Date($('#corrective_action_target_date').val());

    if (correctiveActionDate <= correctionDate) {
      e.preventDefault();
      alert('Corrective action target date must be after correction target date.');
      return false;
    }
  });
});
</script>
@endsection
