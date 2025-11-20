@extends('layouts/layoutMaster')

@section('title', 'User Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">User Details</h4>
      <p class="text-muted mb-0">View complete user information</p>
    </div>
    <div>
      <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">
        <i class="icon-base ti tabler-pencil me-1"></i> Edit User
      </a>
      <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Users
      </a>
    </div>
  </div>

  <div class="row">
    <!-- User Information Card -->
    <div class="col-md-6 col-lg-4 mb-6">
      <div class="card">
        <div class="card-body text-center">
          <div class="avatar avatar-xl mx-auto mb-4">
            <span class="avatar-initial rounded-circle bg-label-primary">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </span>
          </div>
          <h5 class="mb-2">{{ $user->name }}</h5>
          <p class="text-muted mb-4">{{ $user->email }}</p>
          @if($user->is_active)
            <span class="badge bg-label-success">Active</span>
          @else
            <span class="badge bg-label-danger">Inactive</span>
          @endif
        </div>
        <div class="card-body border-top">
          <h6 class="mb-4">Assigned Roles</h6>
          @if($user->roles->count() > 0)
            @foreach($user->roles as $role)
              <span class="badge bg-label-info me-1 mb-1">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
            @endforeach
          @else
            <p class="text-muted mb-0">No roles assigned</p>
          @endif
        </div>
      </div>
    </div>

    <!-- Details Card -->
    <div class="col-md-6 col-lg-8 mb-6">
      <div class="card mb-6">
        <div class="card-header">
          <h5 class="card-title mb-0">Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Full Name</small>
              <p class="mb-0">{{ $user->name }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Email</small>
              <p class="mb-0">{{ $user->email }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Phone</small>
              <p class="mb-0">{{ $user->phone ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Mobile</small>
              <p class="mb-0">{{ $user->mobile ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Language</small>
              <p class="mb-0">{{ $user->language === 'en' ? 'English' : 'Arabic' }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Status</small>
              <p class="mb-0">
                @if($user->is_active)
                  <span class="badge bg-label-success">Active</span>
                @else
                  <span class="badge bg-label-danger">Inactive</span>
                @endif
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Organizational Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Sector</small>
              <p class="mb-0">{{ $user->sector?->name ?? '-' }}</p>
              @if($user->sector)
                <small class="text-muted">{{ $user->sector->code }}</small>
              @endif
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Department</small>
              <p class="mb-0">{{ $user->department?->name ?? '-' }}</p>
              @if($user->department)
                <small class="text-muted">{{ $user->department->code }}</small>
              @endif
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Email Verified</small>
              <p class="mb-0">
                @if($user->email_verified_at)
                  <span class="badge bg-label-success">Verified</span>
                  <br><small class="text-muted">{{ $user->email_verified_at->format('M d, Y') }}</small>
                @else
                  <span class="badge bg-label-warning">Not Verified</span>
                @endif
              </p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Last Login</small>
              <p class="mb-0">
                @if($user->last_login_at)
                  {{ $user->last_login_at->format('M d, Y H:i') }}
                @else
                  Never
                @endif
              </p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Created At</small>
              <p class="mb-0">{{ $user->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="col-md-6 mb-4">
              <small class="text-muted text-uppercase">Updated At</small>
              <p class="mb-0">{{ $user->updated_at->format('M d, Y H:i') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
