@extends('layouts/layoutMaster')

@section('title', 'Audit Plan Details')

@section('vendor-style')
@vite('resources/assets/vendor/libs/sweetalert2/sweetalert2.scss')
@endsection

@section('content')
<!-- Header Section -->
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1">{{ $auditPlan->title }}</h4>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-{{ $auditPlan->status_color }}">
              {{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}
            </span>
            @if($auditPlan->isOverdue())
              <span class="badge bg-label-danger">Overdue</span>
            @endif
            <span class="badge bg-label-info">{{ $auditPlan->audit_type_label }}</span>
            @if(!$auditPlan->is_active)
              <span class="badge bg-label-secondary">Inactive</span>
            @endif
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('audit-plans.index') }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
          </a>

          @if($auditPlan->status === 'planned')
            <form action="{{ route('audit-plans.start', $auditPlan) }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-primary">
                <i class="icon-base ti tabler-player-play me-1"></i> Start Audit
              </button>
            </form>
          @endif

          @if($auditPlan->status === 'in_progress')
            <form action="{{ route('audit-plans.complete', $auditPlan) }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-success">
                <i class="icon-base ti tabler-check me-1"></i> Complete Audit
              </button>
            </form>
          @endif

          @if(!in_array($auditPlan->status, ['completed', 'cancelled']))
            <a href="{{ route('audit-plans.edit', $auditPlan) }}" class="btn btn-sm btn-info">
              <i class="icon-base ti tabler-edit me-1"></i> Edit
            </a>
            <form action="{{ route('audit-plans.cancel', $auditPlan) }}" method="POST" class="d-inline">
              @csrf
              <button type="button" class="btn btn-sm btn-warning btn-cancel-audit">
                <i class="icon-base ti tabler-ban me-1"></i> Cancel
              </button>
            </form>
          @endif

          @if($auditPlan->status !== 'in_progress')
            <form action="{{ route('audit-plans.destroy', $auditPlan) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="button" class="btn btn-sm btn-danger btn-delete-audit">
                <i class="icon-base ti tabler-trash me-1"></i> Delete
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Statistics Cards -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Duration</span>
            <h4 class="mb-0 me-2">{{ $stats['duration'] }} days</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded-circle bg-label-primary">
              <i class="icon-base ti tabler-calendar icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Days Remaining</span>
            <h4 class="mb-0 me-2 {{ $stats['days_remaining'] < 0 ? 'text-danger' : '' }}">
              {{ abs($stats['days_remaining']) }} days
              @if($stats['days_remaining'] < 0) overdue @endif
            </h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded-circle bg-label-{{ $stats['days_remaining'] < 0 ? 'danger' : 'info' }}">
              <i class="icon-base ti tabler-clock icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Status</span>
            <h5 class="mb-0 me-2 badge bg-label-{{ $auditPlan->status_color }}">
              {{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}
            </h5>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded-circle bg-label-{{ $auditPlan->status_color }}">
              <i class="icon-base ti tabler-flag icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Overdue</span>
            <h4 class="mb-0 me-2">{{ $stats['is_overdue'] ? 'Yes' : 'No' }}</h4>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded-circle bg-label-{{ $stats['is_overdue'] ? 'danger' : 'success' }}">
              <i class="icon-base ti tabler-alert-triangle icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Audit Plan Details -->
<div class="row">
  <div class="col-md-6">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Basic Information</h5>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tbody>
            <tr>
              <td class="text-nowrap fw-medium">Audit Type</td>
              <td><span class="badge bg-label-info">{{ $auditPlan->audit_type_label }}</span></td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Sector</td>
              <td>
                @if($auditPlan->sector)
                  {{ $auditPlan->sector->name }} ({{ $auditPlan->sector->code }})
                @else
                  <span class="text-muted">Not specified</span>
                @endif
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Departments</td>
              <td>
                @if($auditPlan->departments->count() > 0)
                  @foreach($auditPlan->departments as $department)
                    <span class="badge bg-label-primary me-1 mb-1">
                      {{ $department->name }} ({{ $department->code }})
                    </span>
                  @endforeach
                @else
                  <span class="text-muted">No departments assigned</span>
                @endif
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Lead Auditor</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-2">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      {{ substr($auditPlan->leadAuditor->name, 0, 2) }}
                    </span>
                  </div>
                  <div>
                    <div>{{ $auditPlan->leadAuditor->name }}</div>
                    <small class="text-muted">{{ $auditPlan->leadAuditor->email }}</small>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Created By</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-2">
                    <span class="avatar-initial rounded-circle bg-label-secondary">
                      {{ substr($auditPlan->creator->name, 0, 2) }}
                    </span>
                  </div>
                  <div>
                    <div>{{ $auditPlan->creator->name }}</div>
                    <small class="text-muted">{{ $auditPlan->created_at->format('d M Y, H:i') }}</small>
                  </div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Active Status</td>
              <td>
                <span class="badge bg-label-{{ $auditPlan->is_active ? 'success' : 'secondary' }}">
                  {{ $auditPlan->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Timeline & Dates</h5>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tbody>
            <tr>
              <td class="text-nowrap fw-medium">Planned Start Date</td>
              <td>{{ $auditPlan->planned_start_date->format('d M Y') }}</td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Planned End Date</td>
              <td>{{ $auditPlan->planned_end_date->format('d M Y') }}</td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Actual Start Date</td>
              <td>
                @if($auditPlan->actual_start_date)
                  {{ $auditPlan->actual_start_date->format('d M Y') }}
                @else
                  <span class="text-muted">Not started yet</span>
                @endif
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Actual End Date</td>
              <td>
                @if($auditPlan->actual_end_date)
                  {{ $auditPlan->actual_end_date->format('d M Y') }}
                @else
                  <span class="text-muted">Not completed yet</span>
                @endif
              </td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Created At</td>
              <td>{{ $auditPlan->created_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
              <td class="text-nowrap fw-medium">Last Updated</td>
              <td>{{ $auditPlan->updated_at->format('d M Y, H:i') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Description, Scope, and Objectives -->
<div class="row">
  @if($auditPlan->description)
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Description</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $auditPlan->description }}</p>
      </div>
    </div>
  </div>
  @endif

  @if($auditPlan->scope)
  <div class="col-md-6">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Audit Scope</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $auditPlan->scope }}</p>
      </div>
    </div>
  </div>
  @endif

  @if($auditPlan->objectives)
  <div class="col-md-6">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Audit Objectives</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $auditPlan->objectives }}</p>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/sweetalert2/sweetalert2.js')
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Delete confirmation
  $('.btn-delete-audit').on('click', function(e) {
    e.preventDefault();
    const form = $(this).closest('form');

    Swal.fire({
      title: 'Are you sure?',
      text: "This audit plan will be deleted!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'No, cancel!',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        form.submit();
      }
    });
  });

  // Cancel confirmation
  $('.btn-cancel-audit').on('click', function(e) {
    e.preventDefault();
    const form = $(this).closest('form');

    Swal.fire({
      title: 'Cancel Audit Plan?',
      text: "This will mark the audit plan as cancelled!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, cancel it!',
      cancelButtonText: 'No, go back!',
      customClass: {
        confirmButton: 'btn btn-warning me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        form.submit();
      }
    });
  });
});
</script>
@endsection
