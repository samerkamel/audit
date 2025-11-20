@extends('layouts/layoutMaster')

@section('title', 'Edit User')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite([
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
      <h4 class="fw-bold mb-1">Edit User</h4>
      <p class="text-muted mb-0">Update user information and roles</p>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Users
    </a>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">User Information</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            @include('users.partials.form', ['submitLabel' => 'Update User'])
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
