@extends('layouts/layoutMaster')

@section('title', 'Register Customer Complaint')

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
      <h4 class="fw-bold mb-1">Register Customer Complaint</h4>
      <p class="text-muted mb-0">Record and track customer feedback and complaints</p>
    </div>
    <a href="{{ route('complaints.index') }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Complaints
    </a>
  </div>

  <form action="{{ route('complaints.store') }}" method="POST">
    @csrf

    <!-- Complaint Information -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-calendar me-2"></i> Complaint Information
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="complaint_date">Complaint Date <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control flatpickr-date @error('complaint_date') is-invalid @enderror"
              id="complaint_date"
              name="complaint_date"
              value="{{ old('complaint_date') }}"
              placeholder="Select complaint date"
              required />
            @error('complaint_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="response_date">Expected Response Date</label>
            <input
              type="text"
              class="form-control flatpickr-date @error('response_date') is-invalid @enderror"
              id="response_date"
              name="response_date"
              value="{{ old('response_date') }}"
              placeholder="Select expected response date" />
            @error('response_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Target date for response</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Information -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-user me-2"></i> Customer Information
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="customer_name">Customer Name <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control @error('customer_name') is-invalid @enderror"
              id="customer_name"
              name="customer_name"
              value="{{ old('customer_name') }}"
              placeholder="Enter customer name"
              required />
            @error('customer_name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="customer_company">Company/Organization</label>
            <input
              type="text"
              class="form-control @error('customer_company') is-invalid @enderror"
              id="customer_company"
              name="customer_company"
              value="{{ old('customer_company') }}"
              placeholder="Enter company name" />
            @error('customer_company')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="customer_email">Email Address</label>
            <input
              type="email"
              class="form-control @error('customer_email') is-invalid @enderror"
              id="customer_email"
              name="customer_email"
              value="{{ old('customer_email') }}"
              placeholder="customer@example.com" />
            @error('customer_email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="customer_phone">Phone Number</label>
            <input
              type="text"
              class="form-control @error('customer_phone') is-invalid @enderror"
              id="customer_phone"
              name="customer_phone"
              value="{{ old('customer_phone') }}"
              placeholder="+1 (555) 000-0000" />
            @error('customer_phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Complaint Details -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-file-description me-2"></i> Complaint Details
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-12">
            <label class="form-label" for="complaint_subject">Subject <span class="text-danger">*</span></label>
            <input
              type="text"
              class="form-control @error('complaint_subject') is-invalid @enderror"
              id="complaint_subject"
              name="complaint_subject"
              value="{{ old('complaint_subject') }}"
              placeholder="Brief description of the complaint"
              required />
            @error('complaint_subject')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="complaint_description">Detailed Description <span class="text-danger">*</span></label>
            <textarea
              class="form-control @error('complaint_description') is-invalid @enderror"
              id="complaint_description"
              name="complaint_description"
              rows="6"
              placeholder="Provide detailed information about the complaint including what happened, when it occurred, and any other relevant details..."
              required>{{ old('complaint_description') }}</textarea>
            @error('complaint_description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label" for="complaint_category">Category <span class="text-danger">*</span></label>
            <select
              class="form-select @error('complaint_category') is-invalid @enderror"
              id="complaint_category"
              name="complaint_category"
              required>
              <option value="">Select category</option>
              <option value="product_quality" {{ old('complaint_category') == 'product_quality' ? 'selected' : '' }}>Product Quality</option>
              <option value="service_quality" {{ old('complaint_category') == 'service_quality' ? 'selected' : '' }}>Service Quality</option>
              <option value="delivery" {{ old('complaint_category') == 'delivery' ? 'selected' : '' }}>Delivery</option>
              <option value="documentation" {{ old('complaint_category') == 'documentation' ? 'selected' : '' }}>Documentation</option>
              <option value="technical_support" {{ old('complaint_category') == 'technical_support' ? 'selected' : '' }}>Technical Support</option>
              <option value="billing" {{ old('complaint_category') == 'billing' ? 'selected' : '' }}>Billing</option>
              <option value="other" {{ old('complaint_category') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('complaint_category')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label" for="priority">Priority <span class="text-danger">*</span></label>
            <select
              class="form-select @error('priority') is-invalid @enderror"
              id="priority"
              name="priority"
              required>
              <option value="">Select priority</option>
              <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
              <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
              <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
              <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('priority')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-4">
            <label class="form-label" for="severity">Severity <span class="text-danger">*</span></label>
            <select
              class="form-select @error('severity') is-invalid @enderror"
              id="severity"
              name="severity"
              required>
              <option value="">Select severity</option>
              <option value="minor" {{ old('severity', 'minor') == 'minor' ? 'selected' : '' }}>Minor</option>
              <option value="major" {{ old('severity') == 'major' ? 'selected' : '' }}>Major</option>
              <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
            @error('severity')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-user-check me-2"></i> Assignment
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="assigned_to_department_id">Assign to Department</label>
            <select
              class="form-select @error('assigned_to_department_id') is-invalid @enderror"
              id="assigned_to_department_id"
              name="assigned_to_department_id">
              <option value="">Select department</option>
              @foreach($departments as $department)
              <option value="{{ $department->id }}" {{ old('assigned_to_department_id') == $department->id ? 'selected' : '' }}>
                {{ $department->name }}
              </option>
              @endforeach
            </select>
            @error('assigned_to_department_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label" for="assigned_to_user_id">Assign to User</label>
            <select
              class="form-select @error('assigned_to_user_id') is-invalid @enderror"
              id="assigned_to_user_id"
              name="assigned_to_user_id">
              <option value="">Select user</option>
              @foreach($users as $user)
              <option value="{{ $user->id }}" {{ old('assigned_to_user_id') == $user->id ? 'selected' : '' }}>
                {{ $user->name }}
              </option>
              @endforeach
            </select>
            @error('assigned_to_user_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- CAR Requirement -->
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="icon-base ti tabler-file-arrow-right me-2"></i> Corrective Action
        </h5>
      </div>
      <div class="card-body">
        <div class="form-check form-switch">
          <input
            class="form-check-input"
            type="checkbox"
            id="car_required"
            name="car_required"
            value="1"
            {{ old('car_required') ? 'checked' : '' }} />
          <label class="form-check-label" for="car_required">
            This complaint requires a Corrective Action Request (CAR)
          </label>
        </div>
        <small class="text-muted">Check this if the complaint requires formal corrective actions</small>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('complaints.index') }}" class="btn btn-label-secondary">
            <i class="icon-base ti tabler-x me-1"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-check me-1"></i> Register Complaint
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
    maxDate: 'today'
  });

  // Response date should be after complaint date
  $('#complaint_date').on('change', function() {
    const complaintDate = $(this).val();
    if (complaintDate) {
      $('#response_date').flatpickr({
        dateFormat: 'Y-m-d',
        minDate: complaintDate
      });
    }
  });
});
</script>
@endsection
