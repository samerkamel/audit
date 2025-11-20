@extends('layouts/layoutMaster')

@section('title', 'Edit CAR')

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
          <div>
            <h5 class="mb-1">Edit CAR: {{ $car->car_number }}</h5>
            <span class="badge bg-label-{{ $car->status_color }}">
              {{ ucwords(str_replace('_', ' ', $car->status)) }}
            </span>
          </div>
          <a href="{{ route('cars.show', $car) }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to CAR
          </a>
        </div>
        <div class="card-body">
          @if($car->status === 'rejected_to_be_edited' && $car->clarification)
          <div class="alert alert-warning d-flex align-items-start mb-6" role="alert">
            <i class="icon-base ti tabler-alert-triangle me-2 icon-24px"></i>
            <div>
              <strong>CAR Rejected for Editing</strong><br>
              <small class="text-muted">Quality Team Clarification:</small><br>
              {{ $car->clarification }}
            </div>
          </div>
          @endif

          <form action="{{ route('cars.update', $car) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-6">
              <!-- CAR Number (Read-only) -->
              <div class="col-md-6">
                <label class="form-label">CAR Number</label>
                <input type="text" class="form-control" value="{{ $car->car_number }}" readonly>
              </div>

              <!-- Issued Date (Read-only) -->
              <div class="col-md-6">
                <label class="form-label">Issued Date</label>
                <input type="text" class="form-control" value="{{ $car->issued_date->format('Y-m-d') }}" readonly>
              </div>

              <!-- Source Type -->
              <div class="col-md-6">
                <label class="form-label" for="source_type">Source Type <span class="text-danger">*</span></label>
                <select class="form-select select2 @error('source_type') is-invalid @enderror"
                        id="source_type"
                        name="source_type"
                        required>
                  <option value="">Select Source Type</option>
                  <option value="internal_audit" {{ old('source_type', $car->source_type) == 'internal_audit' ? 'selected' : '' }}>Internal Audit</option>
                  <option value="external_audit" {{ old('source_type', $car->source_type) == 'external_audit' ? 'selected' : '' }}>External Audit</option>
                  <option value="customer_complaint" {{ old('source_type', $car->source_type) == 'customer_complaint' ? 'selected' : '' }}>Customer Complaint</option>
                  <option value="process_performance" {{ old('source_type', $car->source_type) == 'process_performance' ? 'selected' : '' }}>Process Performance</option>
                  <option value="other" {{ old('source_type', $car->source_type) == 'other' ? 'selected' : '' }}>Other</option>
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
                  <option value="critical" {{ old('priority', $car->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
                  <option value="high" {{ old('priority', $car->priority) == 'high' ? 'selected' : '' }}>High</option>
                  <option value="medium" {{ old('priority', $car->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                  <option value="low" {{ old('priority', $car->priority) == 'low' ? 'selected' : '' }}>Low</option>
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
                  <option value="{{ $department->id }}" {{ old('from_department_id', $car->from_department_id) == $department->id ? 'selected' : '' }}>
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
                  <option value="{{ $department->id }}" {{ old('to_department_id', $car->to_department_id) == $department->id ? 'selected' : '' }}>
                    {{ $department->name }} ({{ $department->code }})
                  </option>
                  @endforeach
                </select>
                @error('to_department_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Subject -->
              <div class="col-12">
                <label class="form-label" for="subject">Subject <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('subject') is-invalid @enderror"
                       id="subject"
                       name="subject"
                       value="{{ old('subject', $car->subject) }}"
                       maxlength="500"
                       required>
                @error('subject')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Maximum 500 characters</small>
              </div>

              <!-- NCR Description -->
              <div class="col-12">
                <label class="form-label" for="ncr_description">Non-Conformance Description <span class="text-danger">*</span></label>
                <textarea class="form-control @error('ncr_description') is-invalid @enderror"
                          id="ncr_description"
                          name="ncr_description"
                          rows="4"
                          required>{{ old('ncr_description', $car->ncr_description) }}</textarea>
                @error('ncr_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Describe the non-conformance or issue that requires corrective action</small>
              </div>

              <!-- Quality Team Clarification -->
              <div class="col-12">
                <label class="form-label" for="clarification">Quality Team Clarification (Optional)</label>
                <textarea class="form-control @error('clarification') is-invalid @enderror"
                          id="clarification"
                          name="clarification"
                          rows="3">{{ old('clarification', $car->clarification) }}</textarea>
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
                  <i class="icon-base ti tabler-device-floppy me-1"></i> Update CAR
                </button>
                <a href="{{ route('cars.show', $car) }}" class="btn btn-label-secondary">
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
});
</script>
@endsection
