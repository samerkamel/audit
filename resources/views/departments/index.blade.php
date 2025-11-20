@extends('layouts/layoutMaster')

@section('title', 'Departments Management')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

<!-- Vendor Scripts -->
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
      <h4 class="fw-bold mb-1">Departments Management</h4>
      <p class="text-muted mb-0">Manage organizational departments</p>
    </div>
    <a href="{{ route('departments.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus me-1"></i> Add Department
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total Departments</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <small class="mb-0">All departments</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-building-community icon-26px"></i>
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
              <span class="text-heading">Active</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['active'] }}</h4>
              </div>
              <small class="mb-0">Currently operational</small>
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
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Inactive</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['inactive'] }}</h4>
              </div>
              <small class="mb-0">Not currently active</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-warning">
                <i class="icon-base ti tabler-circle-x icon-26px"></i>
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
              <span class="text-heading">With Manager</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['with_manager'] }}</h4>
              </div>
              <small class="mb-0">Departments with managers</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-info">
                <i class="icon-base ti tabler-user-star icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Departments List Card -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Departments List</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-departments table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Department</th>
            <th>Sector</th>
            <th>Manager</th>
            <th>Contact</th>
            <th>Users</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($departments as $department)
          <tr>
            <td><span class="fw-medium">{{ $department->code }}</span></td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $department->name }}</span>
                @if($department->name_ar)
                <small class="text-muted" dir="rtl">{{ $department->name_ar }}</small>
                @endif
              </div>
            </td>
            <td>
              <span class="badge bg-label-primary">{{ $department->sector->name }}</span>
            </td>
            <td>
              @if($department->manager)
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $department->manager->name }}</span>
                <small class="text-muted">{{ $department->manager->email }}</small>
              </div>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              @if($department->email || $department->phone)
              <div class="d-flex flex-column">
                @if($department->email)
                <small>{{ $department->email }}</small>
                @endif
                @if($department->phone)
                <small>{{ $department->phone }}</small>
                @endif
              </div>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <span class="badge bg-label-info">{{ $department->users->count() }}</span>
            </td>
            <td>
              @if($department->is_active)
              <span class="badge bg-label-success">Active</span>
              @else
              <span class="badge bg-label-warning">Inactive</span>
              @endif
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('departments.show', $department) }}">
                    <i class="icon-base ti tabler-eye me-1"></i> View
                  </a>
                  <a class="dropdown-item" href="{{ route('departments.edit', $department) }}">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit
                  </a>
                  @if(!$department->is_active)
                  <form action="{{ route('departments.reactivate', $department) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-circle-check me-1"></i> Reactivate
                    </button>
                  </form>
                  @endif
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('departments.destroy', $department) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this department?');">
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
    $('.datatables-departments').DataTable({
      responsive: true,
      order: [[0, 'asc']],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: { search: '', searchPlaceholder: 'Search departments...' }
    });
  });
</script>
@endpush
