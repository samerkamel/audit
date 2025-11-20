@extends('layouts/layoutMaster')

@section('title', 'Sector Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Sector Details</h4>
      <p class="text-muted mb-0">View sector information and structure</p>
    </div>
    <div>
      <a href="{{ route('sectors.edit', $sector) }}" class="btn btn-primary me-2">
        <i class="icon-base ti tabler-edit me-1"></i> Edit Sector
      </a>
      <a href="{{ route('sectors.index') }}" class="btn btn-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Sectors
      </a>
    </div>
  </div>

  <div class="row">
    <!-- Sector Information Card -->
    <div class="col-md-6 col-lg-4 mb-6">
      <div class="card">
        <div class="card-body text-center">
          <div class="avatar avatar-xl mx-auto mb-4">
            <span class="avatar-initial rounded-circle bg-label-primary">
              <i class="icon-base ti tabler-building icon-40px"></i>
            </span>
          </div>
          <h5 class="mb-2">{{ $sector->name }}</h5>
          <p class="text-muted mb-1">{{ $sector->code }}</p>
          @if($sector->name_ar)
          <p class="text-muted mb-4" dir="rtl">{{ $sector->name_ar }}</p>
          @endif
          @if($sector->is_active)
            <span class="badge bg-label-success">Active</span>
          @else
            <span class="badge bg-label-danger">Inactive</span>
          @endif
        </div>
        <div class="card-body border-top">
          <h6 class="mb-4">Sector Director</h6>
          @if($sector->director)
          <div class="d-flex align-items-center mb-3">
            <div class="avatar avatar-sm me-3">
              <span class="avatar-initial rounded-circle bg-label-info">
                {{ strtoupper(substr($sector->director->name, 0, 2)) }}
              </span>
            </div>
            <div>
              <h6 class="mb-0">{{ $sector->director->name }}</h6>
              <small class="text-muted">{{ $sector->director->email }}</small>
            </div>
          </div>
          @else
          <p class="text-muted">No director assigned</p>
          @endif
        </div>
      </div>
    </div>

    <!-- Sector Details -->
    <div class="col-md-6 col-lg-8 mb-6">
      <!-- Statistics -->
      <div class="card mb-6">
        <div class="card-body">
          <h5 class="card-title mb-4">Sector Statistics</h5>
          <div class="row g-4">
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="icon-base ti tabler-building-community icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Departments</small>
                  <h5 class="mb-0">{{ $stats['total_departments'] }}</h5>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="icon-base ti tabler-circle-check icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Active Departments</small>
                  <h5 class="mb-0">{{ $stats['active_departments'] }}</h5>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="icon-base ti tabler-users icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Total Users</small>
                  <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-warning">
                    <i class="icon-base ti tabler-user-check icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">Active Users</small>
                  <h5 class="mb-0">{{ $stats['active_users'] }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      @if($sector->description)
      <div class="card mb-6">
        <div class="card-body">
          <h5 class="card-title mb-3">Description</h5>
          <p class="mb-0">{{ $sector->description }}</p>
        </div>
      </div>
      @endif

      <!-- Metadata -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title mb-4">Metadata</h5>
          <div class="row">
            <div class="col-md-6 mb-3">
              <small class="text-muted d-block">Created At</small>
              <span>{{ $sector->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div class="col-md-6 mb-3">
              <small class="text-muted d-block">Updated At</small>
              <span>{{ $sector->updated_at->format('M d, Y H:i') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Departments Section -->
  @if($sector->departments->count() > 0)
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Departments ({{ $sector->departments->count() }})</h5>
    </div>
    <div class="card-body">
      <div class="row g-4">
        @foreach($sector->departments as $department)
        <div class="col-md-6 col-lg-4">
          <div class="card border">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-1">{{ $department->name }}</h6>
                  <small class="text-muted">{{ $department->code }}</small>
                </div>
                @if($department->is_active)
                  <span class="badge bg-label-success badge-sm">Active</span>
                @else
                  <span class="badge bg-label-warning badge-sm">Inactive</span>
                @endif
              </div>
              @if($department->users->count() > 0)
              <div class="mt-3">
                <small class="text-muted">
                  <i class="icon-base ti tabler-users me-1"></i>
                  {{ $department->users->count() }} users
                </small>
              </div>
              @endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <!-- Users Section -->
  @if($sector->users->count() > 0)
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Users ({{ $sector->users->count() }})</h5>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($sector->users as $user)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded-circle bg-label-primary">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                  </span>
                </div>
                <span class="fw-medium">{{ $user->name }}</span>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->department->name ?? '-' }}</td>
            <td>
              @if($user->is_active)
                <span class="badge bg-label-success">Active</span>
              @else
                <span class="badge bg-label-warning">Inactive</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif
</div>
@endsection
