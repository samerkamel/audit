@extends('layouts/layoutMaster')

@section('title', __('improvement_opportunities.edit_title'))

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
          <h5 class="mb-0">{{ __('improvement_opportunities.edit_title') }}: {{ $improvementOpportunity->io_number }}</h5>
          <a href="{{ route('improvement-opportunities.show', $improvementOpportunity) }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('improvement_opportunities.back_to_details') }}
          </a>
        </div>
        <div class="card-body">
          @if($improvementOpportunity->status === 'rejected_to_be_edited')
          <div class="alert alert-warning d-flex align-items-center mb-6" role="alert">
            <i class="icon-base ti tabler-alert-triangle me-2 icon-24px"></i>
            <div>
              <strong>{{ __('improvement_opportunities.rejected_for_editing') }}</strong><br>
              <small>{{ __('improvement_opportunities.review_feedback_and_resubmit') }}</small>
            </div>
          </div>
          @endif

          <form action="{{ route('improvement-opportunities.update', $improvementOpportunity) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-6">
              <!-- Source Type -->
              <div class="col-md-6">
                <label class="form-label" for="source_type">{{ __('improvement_opportunities.source_type') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('source_type') is-invalid @enderror"
                        id="source_type"
                        name="source_type"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_source_type') }}</option>
                  <option value="internal_audit" {{ old('source_type', $improvementOpportunity->source_type) == 'internal_audit' ? 'selected' : '' }}>{{ __('improvement_opportunities.internal_audit') }}</option>
                  <option value="external_audit" {{ old('source_type', $improvementOpportunity->source_type) == 'external_audit' ? 'selected' : '' }}>{{ __('improvement_opportunities.external_audit') }}</option>
                  <option value="process_review" {{ old('source_type', $improvementOpportunity->source_type) == 'process_review' ? 'selected' : '' }}>{{ __('improvement_opportunities.process_review') }}</option>
                  <option value="management_review" {{ old('source_type', $improvementOpportunity->source_type) == 'management_review' ? 'selected' : '' }}>{{ __('improvement_opportunities.management_review') }}</option>
                  <option value="other" {{ old('source_type', $improvementOpportunity->source_type) == 'other' ? 'selected' : '' }}>{{ __('improvement_opportunities.other') }}</option>
                </select>
                @error('source_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Priority -->
              <div class="col-md-6">
                <label class="form-label" for="priority">{{ __('improvement_opportunities.priority') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('priority') is-invalid @enderror"
                        id="priority"
                        name="priority"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_priority') }}</option>
                  <option value="high" {{ old('priority', $improvementOpportunity->priority) == 'high' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_high') }}</option>
                  <option value="medium" {{ old('priority', $improvementOpportunity->priority) == 'medium' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_medium') }}</option>
                  <option value="low" {{ old('priority', $improvementOpportunity->priority) == 'low' ? 'selected' : '' }}>{{ __('improvement_opportunities.priority_low') }}</option>
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
                <label class="form-label" for="to_department_id">{{ __('improvement_opportunities.responsible_department') }} <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('to_department_id') is-invalid @enderror"
                        id="to_department_id"
                        name="to_department_id"
                        required>
                  <option value="">{{ __('improvement_opportunities.select_department') }}</option>
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
                <label class="form-label" for="issued_date">{{ __('improvement_opportunities.issued_date') }} <span class="text-danger">*</span></label>
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
                <label class="form-label" for="subject">{{ __('improvement_opportunities.subject') }} <span class="text-danger">*</span></label>
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
                <small class="form-text text-muted">{{ __('improvement_opportunities.max_500_chars') }}</small>
              </div>

              <!-- Observation Description -->
              <div class="col-12">
                <label class="form-label" for="observation_description">{{ __('improvement_opportunities.observation_description') }} <span class="text-danger">*</span></label>
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
                <label class="form-label" for="improvement_suggestion">{{ __('improvement_opportunities.improvement_suggestion') }}</label>
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
                <label class="form-label" for="clarification">{{ __('improvement_opportunities.quality_team_clarification') }}</label>
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
                  <i class="icon-base ti tabler-device-floppy me-1"></i> {{ __('improvement_opportunities.update_io') }}
                </button>
                <a href="{{ route('improvement-opportunities.show', $improvementOpportunity) }}" class="btn btn-label-secondary">
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
    defaultDate: '{{ old('issued_date', $improvementOpportunity->issued_date->format('Y-m-d')) }}'
  });
});
</script>
@endsection
