@extends('layouts/layoutMaster')

@section('title', 'Edit Sector')

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
      <h4 class="fw-bold mb-1">Edit Sector</h4>
      <p class="text-muted mb-0">Update sector information</p>
    </div>
    <a href="{{ route('sectors.index') }}" class="btn btn-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Sectors
    </a>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Sector Information</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('sectors.update', $sector) }}" method="POST">
            @csrf
            @method('PUT')
            @include('sectors.partials.form', ['submitLabel' => 'Update Sector'])
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
