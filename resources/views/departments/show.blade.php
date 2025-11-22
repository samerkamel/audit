@extends('layouts/layoutMaster')
@section('title', __('Department Details'))
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div><h4 class="fw-bold mb-1">{{ __('Department Details') }}</h4></div>
    <div>
      <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary me-2">
        <i class="icon-base ti tabler-edit me-1"></i> {{ __('Edit') }}
      </a>
      <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back') }}
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 col-lg-4 mb-6">
      <div class="card">
        <div class="card-body text-center">
          <div class="avatar avatar-xl mx-auto mb-4">
            <span class="avatar-initial rounded-circle bg-label-primary">
              <i class="icon-base ti tabler-building-community icon-40px"></i>
            </span>
          </div>
          <h5 class="mb-2">{{ $department->name }}</h5>
          <p class="text-muted mb-1">{{ $department->code }}</p>
          @if($department->name_ar)
          <p class="text-muted mb-4" dir="rtl">{{ $department->name_ar }}</p>
          @endif
          <span class="badge bg-label-{{ $department->is_active ? 'success' : 'warning' }}">
            {{ $department->is_active ? __('Active') : __('Inactive') }}
          </span>
        </div>
        <div class="card-body border-top">
          <h6 class="mb-3">{{ __('Sector') }}</h6>
          <span class="badge bg-label-primary">{{ $department->sector->name }}</span>
          <h6 class="mb-3 mt-4">{{ __('Manager') }}</h6>
          @if($department->manager)
          <div class="d-flex align-items-center">
            <div class="avatar avatar-sm me-3">
              <span class="avatar-initial rounded-circle bg-label-info">
                {{ strtoupper(substr($department->manager->name, 0, 2)) }}
              </span>
            </div>
            <div>
              <h6 class="mb-0">{{ $department->manager->name }}</h6>
              <small class="text-muted">{{ $department->manager->email }}</small>
            </div>
          </div>
          @else
          <p class="text-muted">{{ __('Not Assigned') }}</p>
          @endif
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-8 mb-6">
      <div class="card mb-6">
        <div class="card-body">
          <h5 class="card-title mb-4">{{ __('Statistics') }}</h5>
          <div class="row g-4">
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="icon-base ti tabler-users icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">{{ __('Total') }} {{ __('Users') }}</small>
                  <h5 class="mb-0">{{ $stats['total_users'] }}</h5>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center">
                <div class="avatar me-3">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="icon-base ti tabler-user-check icon-26px"></i>
                  </span>
                </div>
                <div>
                  <small class="text-muted d-block">{{ __('Active') }} {{ __('Users') }}</small>
                  <h5 class="mb-0">{{ $stats['active_users'] }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      @if($department->email || $department->phone)
      <div class="card mb-6">
        <div class="card-body">
          <h5 class="card-title mb-3">{{ __('Contact') }}</h5>
          @if($department->email)<p class="mb-2"><i class="icon-base ti tabler-mail me-2"></i>{{ $department->email }}</p>@endif
          @if($department->phone)<p class="mb-0"><i class="icon-base ti tabler-phone me-2"></i>{{ $department->phone }}</p>@endif
        </div>
      </div>
      @endif

      @if($department->description)
      <div class="card mb-6">
        <div class="card-body">
          <h5 class="card-title mb-3">{{ __('Description') }}</h5>
          <p class="mb-0">{{ $department->description }}</p>
        </div>
      </div>
      @endif
    </div>
  </div>

  @if($department->users->count() > 0)
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('Users') }} ({{ $department->users->count() }})</h5>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Status') }}</th></tr>
        </thead>
        <tbody>
          @foreach($department->users as $user)
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
            <td>
              <span class="badge bg-label-{{ $user->is_active ? 'success' : 'warning' }}">
                {{ $user->is_active ? __('Active') : __('Inactive') }}
              </span>
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
