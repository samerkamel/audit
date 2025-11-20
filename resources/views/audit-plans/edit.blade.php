@extends('layouts/layoutMaster')

@section('title', 'Edit Audit Plan')

@section('vendor-style')
@vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Audit Plan: {{ $auditPlan->title }}</h5>
        <a href="{{ route('audit-plans.index') }}" class="btn btn-sm btn-secondary">
          <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('audit-plans.update', $auditPlan) }}" method="POST">
          @csrf
          @method('PUT')

          @include('audit-plans.partials.form')

          <div class="row mt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-3">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Update Audit Plan
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
$(document).ready(function() {
  // Initialize Select2 for enhanced dropdowns
  $('.select2').select2({
    placeholder: function() {
      return $(this).data('placeholder');
    },
    allowClear: true,
    width: '100%'
  });

  // Update planned_end_date minimum when planned_start_date changes
  $('#planned_start_date').on('change', function() {
    const startDate = $(this).val();
    $('#planned_end_date').attr('min', startDate);

    // Clear end date if it's before the new start date
    const endDate = $('#planned_end_date').val();
    if (endDate && endDate <= startDate) {
      $('#planned_end_date').val('');
    }
  });

  // Update actual_end_date minimum when actual_start_date changes
  $('#actual_start_date').on('change', function() {
    const startDate = $(this).val();
    $('#actual_end_date').attr('min', startDate);

    // Clear end date if it's before the new start date
    const endDate = $('#actual_end_date').val();
    if (endDate && endDate <= startDate) {
      $('#actual_end_date').val('');
    }
  });
});
</script>
@endsection
