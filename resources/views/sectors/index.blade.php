@extends('layouts/layoutMaster')

@section('title', 'Sectors Management')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Sectors Management</h4>
      <p class="text-muted mb-0">Manage organizational sectors and their structure</p>
    </div>
    <a href="{{ route('sectors.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus me-1"></i> Add Sector
    </a>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total Sectors</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <small class="mb-0">All organizational sectors</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-building icon-26px"></i>
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
              <span class="text-heading">Active Sectors</span>
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
              <span class="text-heading">Inactive Sectors</span>
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
              <span class="text-heading">With Director</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['with_director'] }}</h4>
              </div>
              <small class="mb-0">Sectors with directors</small>
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

  <!-- Sectors List Card -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Sectors List</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-sectors table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Sector Name</th>
            <th>Arabic Name</th>
            <th>Director</th>
            <th>Departments</th>
            <th>Users</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($sectors as $sector)
          <tr>
            <td><span class="fw-medium">{{ $sector->code }}</span></td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $sector->name }}</span>
                @if($sector->description)
                <small class="text-muted text-truncate" style="max-width: 200px;">{{ str($sector->description)->limit(50) }}</small>
                @endif
              </div>
            </td>
            <td>{{ $sector->name_ar ?? '-' }}</td>
            <td>
              @if($sector->director)
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $sector->director->name }}</span>
                <small class="text-muted">{{ $sector->director->email }}</small>
              </div>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <span class="badge bg-label-primary">{{ $sector->departments->count() }}</span>
            </td>
            <td>
              <span class="badge bg-label-info">{{ $sector->users->count() }}</span>
            </td>
            <td>
              @if($sector->is_active)
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
                  <a class="dropdown-item" href="{{ route('sectors.show', $sector) }}">
                    <i class="icon-base ti tabler-eye me-1"></i> View
                  </a>
                  <a class="dropdown-item" href="{{ route('sectors.edit', $sector) }}">
                    <i class="icon-base ti tabler-edit me-1"></i> Edit
                  </a>
                  @if(!$sector->is_active)
                  <form action="{{ route('sectors.reactivate', $sector) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">
                      <i class="icon-base ti tabler-circle-check me-1"></i> Reactivate
                    </button>
                  </form>
                  @endif
                  <div class="dropdown-divider"></div>
                  <form action="{{ route('sectors.destroy', $sector) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this sector?');">
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

<!-- Success/Error Messages -->
@if(session('success'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ session('success') }}',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
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
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });
</script>
@endif
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.datatables-sectors').DataTable({
      responsive: true,
      order: [[0, 'asc']],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: {
        search: '',
        searchPlaceholder: 'Search sectors...'
      }
    });
  });
</script>
@endpush
