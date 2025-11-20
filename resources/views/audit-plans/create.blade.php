@extends('layouts/layoutMaster')

@section('title', 'Create Audit Plan')

@section('vendor-style')
@vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create New Audit Plan</h5>
        <a href="{{ route('audit-plans.index') }}" class="btn btn-sm btn-secondary">
          <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('audit-plans.store') }}" method="POST">
          @csrf

          @include('audit-plans.partials.form')

          <div class="row mt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-3">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Create Audit Plan
              </button>
              <a href="{{ route('audit-plans.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
<script>
// Wait for jQuery to be available
(function() {
  function initAuditPlanForm() {
    if (typeof jQuery === 'undefined') {
      setTimeout(initAuditPlanForm, 50);
      return;
    }

  jQuery(document).ready(function($) {
  let departmentIndex = 0;

  // Pre-generate options HTML for departments and auditors
  const departmentOptions = `
    <option value="">Select Department</option>
    @foreach($departments as $department)
      <option value="{{ $department->id }}">{{ $department->name }} ({{ $department->code }})</option>
    @endforeach
  `;

  const auditorOptions = `
    @foreach($auditors as $auditor)
      <option value="{{ $auditor->id }}">{{ $auditor->name }} ({{ $auditor->email }})</option>
    @endforeach
  `;

  // Initialize Select2 for enhanced dropdowns
  $('.select2').select2({
    placeholder: function() {
      return $(this).data('placeholder');
    },
    allowClear: true,
    width: '100%'
  });

  // Initialize existing Select2 instances
  initializeSelect2InContainer($('#departmentsContainer'));

  // Department Template
  function getDepartmentTemplate(index) {
    return `
      <div class="card mb-3 department-section" data-index="${index}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Department ${index}</h6>
            <button type="button" class="btn btn-sm btn-danger remove-department">
              <i class="icon-base ti tabler-trash"></i> Remove
            </button>
          </div>

          <div class="row">
            <!-- Department Selection -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Department <span class="text-danger">*</span></label>
              <select class="form-select select2-department" name="departments[${index}][department_id]" required>
                ${departmentOptions}
              </select>
            </div>

            <!-- Auditors Selection -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Assigned Auditors</label>
              <select class="form-select select2-auditors" name="departments[${index}][auditor_ids][]" multiple>
                ${auditorOptions}
              </select>
              <small class="text-muted">Select auditors for this department</small>
            </div>

            <!-- Planned Start Date -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Planned Start Date</label>
              <input type="date" class="form-control" name="departments[${index}][planned_start_date]">
            </div>

            <!-- Planned End Date -->
            <div class="col-md-6 mb-3">
              <label class="form-label">Planned End Date</label>
              <input type="date" class="form-control" name="departments[${index}][planned_end_date]">
            </div>

            <!-- Notes -->
            <div class="col-12 mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" name="departments[${index}][notes]" rows="2"
                placeholder="Additional notes for this department's audit"></textarea>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Initialize Select2 in a container
  function initializeSelect2InContainer($container) {
    $container.find('.select2-department').select2({
      placeholder: 'Select Department',
      allowClear: true,
      width: '100%'
    });

    $container.find('.select2-auditors').select2({
      placeholder: 'Select Auditors',
      allowClear: true,
      width: '100%'
    });
  }

  // Add Department Button
  $('#addDepartment').on('click', function() {
    departmentIndex++;
    const $container = $('#departmentsContainer');

    // Remove empty state alert if it exists
    $container.find('.alert-info').remove();

    // Add new department section
    const $newSection = $(getDepartmentTemplate(departmentIndex));
    $container.append($newSection);

    // Initialize Select2 for the new section
    initializeSelect2InContainer($newSection);
  });

  // Remove Department Button (delegated event)
  $(document).on('click', '.remove-department', function() {
    const $section = $(this).closest('.department-section');
    $section.fadeOut(300, function() {
      $(this).remove();

      // Show empty state if no departments left
      if ($('#departmentsContainer .department-section').length === 0) {
        $('#departmentsContainer').html(`
          <div class="alert alert-info">
            <i class="icon-base ti tabler-info-circle me-2"></i>
            Click "Add Department" to start adding departments to this audit plan.
          </div>
        `);
      }
    });
  });

  // Form validation
  $('form').on('submit', function(e) {
    const departmentCount = $('#departmentsContainer .department-section').length;
    if (departmentCount === 0) {
      e.preventDefault();
      alert('Please add at least one department to the audit plan.');
      return false;
    }
  });
  });
  }
  initAuditPlanForm();
})();
</script>
@endsection
