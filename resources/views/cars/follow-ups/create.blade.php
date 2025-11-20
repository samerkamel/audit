@extends('layouts/layoutMaster')

@section('title', 'Create CAR Follow-up')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Create Follow-up for CAR {{ $car->car_number }}</h4>
      <p class="text-muted mb-0">Conduct effectiveness review and verification</p>
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
      </div>
    </div>
  </div>

  <!-- Follow-up Form -->
  <form action="{{ route('cars.follow-ups.store', $car) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Follow-up Details -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-calendar-check me-2"></i> Follow-up Details
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="follow_up_date">Follow-up Date <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control flatpickr-date @error('follow_up_date') is-invalid @enderror"
              id="follow_up_date"
              name="follow_up_date"
              value="{{ old('follow_up_date') }}"
              placeholder="Select follow-up date"
              required />
            @error('follow_up_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Date when the follow-up was conducted</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Effectiveness Review -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-checks me-2"></i> Effectiveness Review
        </h5>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <label class="form-label" for="effectiveness_review">Effectiveness Assessment <span class="text-danger">*</span></label>
          <textarea
            class="form-control @error('effectiveness_review') is-invalid @enderror"
            id="effectiveness_review"
            name="effectiveness_review"
            rows="8"
            placeholder="Evaluate whether the corrective actions have been effective in eliminating the root cause and preventing recurrence..."
            required>{{ old('effectiveness_review') }}</textarea>
          @error('effectiveness_review')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Describe the effectiveness of implemented corrective actions</small>
        </div>

        <div class="mb-0">
          <label class="form-label" for="verification_evidence">Verification Evidence</label>
          <textarea
            class="form-control @error('verification_evidence') is-invalid @enderror"
            id="verification_evidence"
            name="verification_evidence"
            rows="6"
            placeholder="Describe objective evidence that supports your effectiveness assessment (e.g., metrics, observations, test results)...">{{ old('verification_evidence') }}</textarea>
          @error('verification_evidence')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Provide objective evidence supporting the effectiveness assessment</small>
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
        <div class="mb-4">
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
            Upload photos, documents, or test results (PDF, Word, Excel, Images). Max 10MB per file.
          </small>
        </div>

        <div class="mb-0">
          <label class="form-label" for="remarks">Additional Remarks</label>
          <textarea
            class="form-control @error('remarks') is-invalid @enderror"
            id="remarks"
            name="remarks"
            rows="4"
            placeholder="Any additional notes or observations...">{{ old('remarks') }}</textarea>
          @error('remarks')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
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
            <i class="icon-base ti tabler-check me-1"></i> Create Follow-up
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
  // Initialize Flatpickr for date picker
  $('.flatpickr-date').flatpickr({
    dateFormat: 'Y-m-d',
    maxDate: 'today',
    defaultDate: 'today'
  });
});
</script>
@endsection
