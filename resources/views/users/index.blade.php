@extends('layouts/layoutMaster')

@section('title', 'User Management')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <h4 class="fw-bold">User Management</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus me-1"></i> Add User
    </a>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <!-- Users Stats -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $users->total() }}</h4>
              </div>
              <small class="mb-0">All system users</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-users icon-26px"></i>
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
              <span class="text-heading">Active Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $users->where('is_active', true)->count() }}</h4>
              </div>
              <small class="mb-0">Currently active</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-success">
                <i class="icon-base ti tabler-user-check icon-26px"></i>
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
              <span class="text-heading">Inactive Users</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $users->where('is_active', false)->count() }}</h4>
              </div>
              <small class="mb-0">Deactivated accounts</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="icon-base ti tabler-user-x icon-26px"></i>
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
              <span class="text-heading">Roles</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $roles->count() }}</h4>
              </div>
              <small class="mb-0">System roles</small>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-info">
                <i class="icon-base ti tabler-shield-lock icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Users List</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-users table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Sector</th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          <tr>
            <td>
              <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-4">
                    <span class="avatar-initial rounded-circle bg-label-{{ $user->is_active ? 'primary' : 'secondary' }}">
                      {{ strtoupper(substr($user->name, 0, 2)) }}
                    </span>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <a href="{{ route('users.show', $user) }}" class="text-heading text-truncate">
                    <span class="fw-medium">{{ $user->name }}</span>
                  </a>
                  <small>{{ $user->phone }}</small>
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>
              @foreach($user->roles as $role)
                <span class="badge bg-label-info me-1">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
              @endforeach
            </td>
            <td>{{ $user->sector?->name ?? '-' }}</td>
            <td>{{ $user->department?->name ?? '-' }}</td>
            <td>
              @if($user->is_active)
                <span class="badge bg-label-success">Active</span>
              @else
                <span class="badge bg-label-danger">Inactive</span>
              @endif
            </td>
            <td>
              <div class="d-inline-block">
                <a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="{{ route('users.show', $user) }}" class="dropdown-item">
                    <i class="icon-base ti tabler-eye me-2"></i>View
                  </a>
                  <a href="{{ route('users.edit', $user) }}" class="dropdown-item">
                    <i class="icon-base ti tabler-pencil me-2"></i>Edit
                  </a>
                  @if($user->is_active)
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to deactivate this user?')">
                        <i class="icon-base ti tabler-user-x me-2"></i>Deactivate
                      </button>
                    </form>
                  @else
                    <form action="{{ route('users.reactivate', $user) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="dropdown-item text-success">
                        <i class="icon-base ti tabler-user-check me-2"></i>Reactivate
                      </button>
                    </form>
                  @endif
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $users->links() }}
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.datatables-users').DataTable({
      paging: false,
      searching: true,
      ordering: true,
      info: false,
      responsive: true,
      language: {
        search: '',
        searchPlaceholder: 'Search users...'
      }
    });
  });
</script>
@endpush
