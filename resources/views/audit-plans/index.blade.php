@extends('layouts/layoutMaster')

@section('title', 'Audit Plans Management')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Audit Plans Management</h4>
      <p class="text-muted mb-0">Plan and schedule organizational audits</p>
    </div>
    <a href="{{ route('audit-plans.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus me-1"></i> Create Audit Plan
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-clipboard-check icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Draft</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['draft'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ti tabler-file-text icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Planned</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['planned'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-info">
                <i class="icon-base ti tabler-calendar icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">In Progress</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['in_progress'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-progress icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Completed</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['completed'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-success">
                <i class="icon-base ti tabler-circle-check icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-2">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Overdue</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['overdue'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-danger">
                <i class="icon-base ti tabler-alert-circle icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Audit Plans List Card -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Audit Plans List</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-audit-plans table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Sector</th>
            <th>Lead Auditor</th>
            <th>Planned Dates</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($auditPlans as $plan)
          <tr>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $plan->title }}</span>
                @if($plan->departments->count() > 0)
                <small class="text-muted">
                  {{ $plan->departments->pluck('name')->implode(', ') }}
                  @if($plan->departments->count() > 3)
                    <span class="badge bg-label-secondary ms-1">+{{ $plan->departments->count() - 3 }} more</span>
                  @endif
                </small>
                @endif
              </div>
            </td>
            <td>
              <span class="badge bg-label-info">{{ $plan->audit_type_label }}</span>
            </td>
            <td>{{ $plan->sector?->name ?? '-' }}</td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $plan->leadAuditor?->name ?? 'Not Assigned' }}</span>
                <small class="text-muted">{{ $plan->leadAuditor?->email ?? '' }}</small>
              </div>
            </td>
            <td>
              <div class="d-flex flex-column">
                <small>{{ $plan->planned_start_date?->format('d M Y') ?? '-' }}</small>
                <small>{{ $plan->planned_end_date?->format('d M Y') ?? '-' }}</small>
              </div>
            </td>
            <td>
              <span class="badge bg-label-{{ $plan->status_color }}">{{ ucfirst(str_replace('_', ' ', $plan->status)) }}</span>
              @if($plan->isOverdue())
              <span class="badge bg-label-danger ms-1">Overdue</span>
              @endif
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('audit-plans.show', $plan) }}">
                    <i class="icon-base ti tabler-eye me-1"></i> View
                  </a>
                  <a class="dropdown-item" href="{{ route('audit-plans.edit', $plan) }}">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit
                  </a>
                  @if($plan->status === 'planned')
                  <form action="{{ route('audit-plans.start', $plan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-player-play me-1"></i> Start
                    </button>
                  </form>
                  @endif
                  @if($plan->status === 'in_progress')
                  <form action="{{ route('audit-plans.complete', $plan) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-circle-check me-1"></i> Complete
                    </button>
                  </form>
                  @endif
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('audit-plans.destroy', $plan) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this audit plan?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="icon-base ti tabler-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

@if(session('success'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ session('success') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif

@if(session('error'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: '{{ session('error') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.datatables-audit-plans').DataTable({
      responsive: true,
      order: [[0, 'desc']],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { search: '', searchPlaceholder: 'Search audit plans...' }
    });
  });
</script>
@endpush
