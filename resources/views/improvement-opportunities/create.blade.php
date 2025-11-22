@extends('layouts/layoutMaster')

@section('title', 'Create Improvement Opportunity')

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
          <h5 class="mb-0">Create Improvement Opportunity</h5>
          <a href="{{ route('improvement-opportunities.index') }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
          </a>
        </div>
        <div class="card-body">
          @if($auditFinding)
          <div class="alert alert-info d-flex align-items-center mb-6" role="alert">
            <i class="icon-base ti tabler-info-circle me-2 icon-24px"></i>
            <div>
              <strong>Creating IO from Audit Observation</strong><br>
              <small>
                Audit: {{ $auditFinding->audit->auditPlan->plan_name }}<br>
                Department: {{ $auditFinding->audit->auditPlan->department->name }}<br>
                Observation: {{ $auditFinding->question_text }}
              </small>
            </div>
          </div>
          @endif

          <form action="{{ route('improvement-opportunities.store') }}" method="POST">
            @csrf

            <div class="row g-6">
              <!-- Source Type -->
              <div class="col-md-6">
                <label class="form-label" for="source_type">Source Type <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('source_type') is-invalid @enderror"
                        id="source_type"
                        name="source_type"
                        required>
                  <option value="">Select Source Type</option>
                  <option value="internal_audit" {{ old('source_type', $auditFinding ? 'internal_audit' : '') == 'internal_audit' ? 'selected' : '' }}>Internal Audit</option>
                  <option value="external_audit" {{ old('source_type') == 'external_audit' ? 'selected' : '' }}>External Audit</option>
                  <option value="process_review" {{ old('source_type') == 'process_review' ? 'selected' : '' }}>Process Review</option>
                  <option value="management_review" {{ old('source_type') == 'management_review' ? 'selected' : '' }}>Management Review</option>
                  <option value="other" {{ old('source_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('source_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Audit Finding ID (if creating from audit finding) -->
              @if($auditFinding)
              <input type="hidden" name="audit_finding_id" value="{{ $auditFinding->id }}">
              @endif

              <!-- Priority -->
              <div class="col-md-6">
                <label class="form-label" for="priority">Priority <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('priority') is-invalid @enderror"
                        id="priority"
                        name="priority"
                        required>
                  <option value="">Select Priority</option>
                  <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                  <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                  <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
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
                  <option value="{{ $department->id }}" {{ old('from_department_id', auth()->user()->department_id ?? '') == $department->id ? 'selected' : '' }}>
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
                  <option value="{{ $department->id }}"
                          {{ old('to_department_id', $auditFinding ? $auditFinding->audit->auditPlan->department_id : '') == $department->id ? 'selected' : '' }}>
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
                       value="{{ old('issued_date', date('Y-m-d')) }}"
                       placeholder="YYYY-MM-DD"
                       required>
                @error('issued_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Status -->
              <div class="col-md-6">
                <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror"
                        id="status"
                        name="status"
                        required>
                  <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                  Save as Draft to edit later, or submit for approval directly.
                </small>
              </div>

              <!-- Subject -->
              <div class="col-12">
                <label class="form-label" for="subject">Subject <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('subject') is-invalid @enderror"
                       id="subject"
                       name="subject"
                       value="{{ old('subject', $auditFinding ? 'Observation: ' . $auditFinding->question_text : '') }}"
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
                          required>{{ old('observation_description', $auditFinding ? $auditFinding->auditor_remarks : '') }}</textarea>
                @error('observation_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe the observation or area for improvement</small>
              </div>

              <!-- Improvement Suggestion -->
              <div class="col-12">
                <label class="form-label" for="improvement_suggestion">Improvement Suggestion (Optional)</label>
                <textarea class="form-control @error('improvement_suggestion') is-invalid @enderror"
                          id="improvement_suggestion"
                          name="improvement_suggestion"
                          rows="3">{{ old('improvement_suggestion') }}</textarea>
                @error('improvement_suggestion')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Suggested improvements or recommendations</small>
              </div>

              <!-- Quality Team Clarification -->
              <div class="col-12">
                <label class="form-label" for="clarification">Quality Team Clarification (Optional)</label>
                <textarea class="form-control @error('clarification') is-invalid @enderror"
                          id="clarification"
                          name="clarification"
                          rows="3">{{ old('clarification') }}</textarea>
                @error('clarification')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Additional clarifications or instructions from quality team</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-6">
              <div class="col-12">
                <button type="submit" class="btn btn-primary me-3">
                  <i class="icon-base ti tabler-device-floppy me-1"></i> Create IO
                </button>
                <a href="{{ route('improvement-opportunities.index') }}" class="btn btn-label-secondary">
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
    defaultDate: '{{ old('issued_date', date('Y-m-d')) }}'
  });
});
</script>
@endsection
