@extends('layouts/layoutMaster')

@section('title', __('improvement_opportunities.create_title'))

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
          <h5 class="mb-0">{{ __('improvement_opportunities.create_title') }}</h5>
          <a href="{{ route('improvement-opportunities.index') }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('improvement_opportunities.back_to_list') }}
          </a>
        </div>
        <div class="card-body">
          @if($auditFinding)
          <div class="alert alert-info d-flex align-items-center mb-6" role="alert">
            <i class="icon-base ti tabler-info-circle me-2 icon-24px"></i>
            <div>
              <strong>{{ __('improvement_opportunities.creating_from_audit') }}</strong><br>
              <small>
                {{ __('improvement_opportunities.audit') }}: {{ $auditFinding->audit->auditPlan->plan_name }}<br>
                {{ __('improvement_opportunities.department') }}: {{ $auditFinding->audit->auditPlan->department->name }}<br>
                {{ __('improvement_opportunities.observation') }}: {{ $auditFinding->question_text }}
              </small>
            </div>
          </div>
          @endif

          <form action="{{ route('improvement-opportunities.store') }}" method="POST">
            @csrf

            <div class="row g-6">
              <!-- Source Type -->
              <div class="col-md-6">
                <label class="form-label" for="source_type">{{ __('improvement_opportunities.source_type') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('source_type') is-invalid @enderror"
                        id="source_type"
                        name="source_type"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_source_type') }}</option>
                  <option value="internal_audit" {{ old('source_type', $auditFinding ? 'internal_audit' : '') == 'internal_audit' ? 'selected' : '' }}>{{ __('improvement_opportunities.internal_audit') }}</option>
                  <option value="external_audit" {{ old('source_type') == 'external_audit' ? 'selected' : '' }}>{{ __('improvement_opportunities.external_audit') }}</option>
                  <option value="process_review" {{ old('source_type') == 'process_review' ? 'selected' : '' }}>{{ __('improvement_opportunities.process_review') }}</option>
                  <option value="management_review" {{ old('source_type') == 'management_review' ? 'selected' : '' }}>{{ __('improvement_opportunities.management_review') }}</option>
                  <option value="other" {{ old('source_type') == 'other' ? 'selected' : '' }}>{{ __('improvement_opportunities.other') }}</option>
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
                <label class="form-label" for="priority">{{ __('improvement_opportunities.priority') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('priority') is-invalid @enderror"
                        id="priority"
                        name="priority"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_priority') }}</option>
                  <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_high') }}</option>
                  <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_medium') }}</option>
                  <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_low') }}</option>
                </select>
                @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- From Department -->
              <div class="col-md-6">
                <label class="form-label" for="from_department_id">{{ __('improvement_opportunities.issued_by_department') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('from_department_id') is-invalid @enderror"
                        id="from_department_id"
                        name="from_department_id"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_department') }}</option>
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
                <label class="form-label" for="to_department_id">{{ __('improvement_opportunities.responsible_department') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('to_department_id') is-invalid @enderror"
                        id="to_department_id"
                        name="to_department_id"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_department') }}</option>
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
                <label class="form-label" for="issued_date">{{ __('improvement_opportunities.issued_date') }} <span class="text-danger">*</span></label>
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
                <label class="form-label" for="status">{{ __('improvement_opportunities.status') }} <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror"
                        id="status"
                        name="status"
                        required>
                  <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>{{ __('improvement_opportunities.status_draft') }}</option>
                  <option value="pending_approval" {{ old('status') == 'pending_approval' ? 'selected' : '' }}>{{ __('improvement_opportunities.status_pending_approval') }}</option>
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                  {{ __('improvement_opportunities.status_helper') }}
                </small>
              </div>

              <!-- Subject -->
              <div class="col-12">
                <label class="form-label" for="subject">{{ __('improvement_opportunities.subject') }} <span class="text-danger">*</span></label>
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
                <small class="form-text text-muted">{{ __('improvement_opportunities.max_500_chars') }}</small>
              </div>

              <!-- Observation Description -->
              <div class="col-12">
                <label class="form-label" for="observation_description">{{ __('improvement_opportunities.observation_description') }} <span class="text-danger">*</span></label>
                <textarea class="form-control @error('observation_description') is-invalid @enderror"
                          id="observation_description"
                          name="observation_description"
                          rows="4"
                          required>{{ old('observation_description', $auditFinding ? $auditFinding->auditor_remarks : '') }}</textarea>
                @error('observation_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('improvement_opportunities.observation_description_helper') }}</small>
              </div>

              <!-- Improvement Suggestion -->
              <div class="col-12">
                <label class="form-label" for="improvement_suggestion">{{ __('improvement_opportunities.improvement_suggestion') }}</label>
                <textarea class="form-control @error('improvement_suggestion') is-invalid @enderror"
                          id="improvement_suggestion"
                          name="improvement_suggestion"
                          rows="3">{{ old('improvement_suggestion') }}</textarea>
                @error('improvement_suggestion')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('improvement_opportunities.improvement_suggestion_helper') }}</small>
              </div>

              <!-- Quality Team Clarification -->
              <div class="col-12">
                <label class="form-label" for="clarification">{{ __('improvement_opportunities.quality_team_clarification') }}</label>
                <textarea class="form-control @error('clarification') is-invalid @enderror"
                          id="clarification"
                          name="clarification"
                          rows="3">{{ old('clarification') }}</textarea>
                @error('clarification')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('improvement_opportunities.quality_team_clarification_helper') }}</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-6">
              <div class="col-12">
                <button type="submit" class="btn btn-primary me-3">
                  <i class="icon-base ti tabler-device-floppy me-1"></i> {{ __('improvement_opportunities.create_io') }}
                </button>
                <a href="{{ route('improvement-opportunities.index') }}" class="btn btn-label-secondary">
                  <i class="icon-base ti tabler-x me-1"></i> {{ __('improvement_opportunities.cancel') }}
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
