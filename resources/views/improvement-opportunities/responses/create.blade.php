@extends('layouts/layoutMaster')

@section('title', 'Add Response - ' . $improvementOpportunity->io_number)

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">Add Response</h5>
            <small class="text-muted">IO: {{ $improvementOpportunity->io_number }}</small>
          </div>
          <a href="{{ route('improvement-opportunities.show', $improvementOpportunity) }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Details
          </a>
        </div>
        <div class="card-body">
          <div class="alert alert-info mb-4">
            <h6 class="alert-heading fw-bold mb-1">{{ $improvementOpportunity->subject }}</h6>
            <p class="mb-0">{{ $improvementOpportunity->observation_description }}</p>
            @if($improvementOpportunity->improvement_suggestion)
            <hr>
            <p class="mb-0"><strong>Suggested Improvement:</strong> {{ $improvementOpportunity->improvement_suggestion }}</p>
            @endif
          </div>

          <form action="{{ route('improvement-opportunity-responses.store', $improvementOpportunity) }}" method="POST">
            @csrf

            <div class="row g-6">
              <!-- Proposed Action -->
              <div class="col-12">
                <label class="form-label" for="proposed_action">Proposed Improvement Action <span class="text-danger">*</span></label>
                <textarea class="form-control @error('proposed_action') is-invalid @enderror"
                          id="proposed_action"
                          name="proposed_action"
                          rows="4"
                          required>{{ old('proposed_action') }}</textarea>
                @error('proposed_action')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe the improvement action you plan to take to address this observation.</small>
              </div>

              <!-- Implementation Plan -->
              <div class="col-12">
                <label class="form-label" for="implementation_plan">Implementation Plan (Optional)</label>
                <textarea class="form-control @error('implementation_plan') is-invalid @enderror"
                          id="implementation_plan"
                          name="implementation_plan"
                          rows="3">{{ old('implementation_plan') }}</textarea>
                @error('implementation_plan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe how you plan to implement the improvement action.</small>
              </div>

              <!-- Target Date -->
              <div class="col-md-6">
                <label class="form-label" for="target_date">Target Completion Date <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control flatpickr-date @error('target_date') is-invalid @enderror"
                       id="target_date"
                       name="target_date"
                       value="{{ old('target_date') }}"
                       placeholder="YYYY-MM-DD"
                       required>
                @error('target_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Expected Outcome -->
              <div class="col-12">
                <label class="form-label" for="outcome">Expected Outcome (Optional)</label>
                <textarea class="form-control @error('outcome') is-invalid @enderror"
                          id="outcome"
                          name="outcome"
                          rows="3">{{ old('outcome') }}</textarea>
                @error('outcome')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe the expected results or benefits of this improvement.</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-6">
              <div class="col-12">
                <button type="submit" class="btn btn-primary me-3">
                  <i class="icon-base ti tabler-send me-1"></i> Submit Response
                </button>
                <a href="{{ route('improvement-opportunities.show', $improvementOpportunity) }}" class="btn btn-label-secondary">
                  <i class="icon-base ti tabler-x me-1"></i> Cancel
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize Flatpickr for date picker
  $('.flatpickr-date').flatpickr({
    dateFormat: 'Y-m-d',
    minDate: 'today'
  });
});
</script>
@endsection
