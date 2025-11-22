@extends('layouts/layoutMaster')

@section('title', 'Edit Improvement Opportunity')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-12">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Edit Improvement Opportunity: {{ $improvementOpportunity->io_number }}</h5>
          <a href="{{ route('improvement-opportunities.show', $improvementOpportunity) }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Details
          </a>
        </div>
        <div class="card-body">
          @if($improvementOpportunity->status === 'rejected_to_be_edited')
          <div class="alert alert-warning d-flex align-items-center mb-6" role="alert">
            <i class="icon-base ti tabler-alert-triangle me-2 icon-24px"></i>
            <div>
              <strong>This IO was rejected for editing</strong><br>
              <small>Please review the feedback and make necessary changes before resubmitting.</small>
            </div>
          </div>
          @endif

          <form action="{{ route('improvement-opportunities.update', $improvementOpportunity) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-6">
              <!-- Source Type -->
              <div class="col-md-6">
                <label class="form-label" for="source_type">Source Type <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('source_type') is-invalid @enderror"
                        id="source_type"
                        name="source_type"
                        required>
                  <option value="">Select Source Type</option>
                  <option value="internal_audit" {{ old('source_type', $improvementOpportunity->source_type) == 'internal_audit' ? 'selected' : '' }}>Internal Audit</option>
                  <option value="external_audit" {{ old('source_type', $improvementOpportunity->source_type) == 'external_audit' ? 'selected' : '' }}>External Audit</option>
                  <option value="process_review" {{ old('source_type', $improvementOpportunity->source_type) == 'process_review' ? 'selected' : '' }}>Process Review</option>
                  <option value="management_review" {{ old('source_type', $improvementOpportunity->source_type) == 'management_review' ? 'selected' : '' }}>Management Review</option>
                  <option value="other" {{ old('source_type', $improvementOpportunity->source_type) == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('source_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Priority -->
              <div class="col-md-6">
                <label class="form-label" for="priority">Priority <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('priority') is-invalid @enderror"
                        id="priority"
                        name="priority"
                        required>
                  <option value="">Select Priority</option>
                  <option value="high" {{ old('priority', $improvementOpportunity->priority) == 'high' ? 'selected' : '' }}>High</option>
                  <option value="medium" {{ old('priority', $improvementOpportunity->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                  <option value="low" {{ old('priority', $improvementOpportunity->priority) == 'low' ? 'selected' : '' }}>Low</option>
                </select>
                @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- From Department -->
              <div class="col-md-6">
                <label class="form-label" for="from_department_id">Issued By (Department) <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('from_department_id') is-invalid @enderror"
                        id="from_department_id"
                        name="from_department_id"
                        required>
                  <option value="">Select Department</option>
                  @foreach($departments as $department)
                  <option value="{{ $department->id }}" {{ old('from_department_id', $improvementOpportunity->from_department_id) == $department->id ? 'selected' : '' }}>
                    {{ $department->name }} ({{ $department->code }})
                  </option>
                  @endforeach
                </select>
                @error('from_department_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- To Department -->
              <div class="col-md-6">
                <label class="form-label" for="to_department_id">Responsible Department <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('to_department_id') is-invalid @enderror"
                        id="to_department_id"
                        name="to_department_id"
                        required>
                  <option value="">Select Department</option>
                  @foreach($departments as $department)
                  <option value="{{ $department->id }}" {{ old('to_department_id', $improvementOpportunity->to_department_id) == $department->id ? 'selected' : '' }}>
                    {{ $department->name }} ({{ $department->code }})
                  </option>
                  @endforeach
                </select>
                @error('to_department_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Issued Date -->
              <div class="col-md-6">
                <label class="form-label" for="issued_date">Issued Date <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control flatpickr-date @error('issued_date') is-invalid @enderror"
                       id="issued_date"
                       name="issued_date"
                       value="{{ old('issued_date', $improvementOpportunity->issued_date->format('Y-m-d')) }}"
                       placeholder="YYYY-MM-DD"
                       required>
                @error('issued_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6"></div>

              <!-- Subject -->
              <div class="col-12">
                <label class="form-label" for="subject">Subject <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('subject') is-invalid @enderror"
                       id="subject"
                       name="subject"
                       value="{{ old('subject', $improvementOpportunity->subject) }}"
                       maxlength="500"
                       required>
                @error('subject')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Maximum 500 characters</small>
              </div>

              <!-- Observation Description -->
              <div class="col-12">
                <label class="form-label" for="observation_description">Observation Description <span class="text-danger">*</span></label>
                <textarea class="form-control @error('observation_description') is-invalid @enderror"
                          id="observation_description"
                          name="observation_description"
                          rows="4"
                          required>{{ old('observation_description', $improvementOpportunity->observation_description) }}</textarea>
                @error('observation_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Improvement Suggestion -->
              <div class="col-12">
                <label class="form-label" for="improvement_suggestion">Improvement Suggestion (Optional)</label>
                <textarea class="form-control @error('improvement_suggestion') is-invalid @enderror"
                          id="improvement_suggestion"
                          name="improvement_suggestion"
                          rows="3">{{ old('improvement_suggestion', $improvementOpportunity->improvement_suggestion) }}</textarea>
                @error('improvement_suggestion')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Quality Team Clarification -->
              <div class="col-12">
                <label class="form-label" for="clarification">Quality Team Clarification (Optional)</label>
                <textarea class="form-control @error('clarification') is-invalid @enderror"
                          id="clarification"
                          name="clarification"
                          rows="3">{{ old('clarification', $improvementOpportunity->clarification) }}</textarea>
                @error('clarification')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-6">
              <div class="col-12">
                <button type="submit" class="btn btn-primary me-3">
                  <i class="icon-base ti tabler-device-floppy me-1"></i> Update IO
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
  // Initialize Select2
  $('.select2').select2({
    theme: 'bootstrap-5',
    placeholder: 'Select an option',
    allowClear: true
  });

  // Initialize Flatpickr for date picker
  $('.flatpickr-date').flatpickr({
    dateFormat: 'Y-m-d',
    defaultDate: '{{ old('issued_date', $improvementOpportunity->issued_date->format('Y-m-d')) }}'
  });
});
</script>
@endsection
