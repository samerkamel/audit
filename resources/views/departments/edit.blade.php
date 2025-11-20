@extends('layouts/layoutMaster')
@section('title', 'Edit Department')
@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection
@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Edit Department</h4>
      <p class="text-muted mb-0">Update department information</p>
    </div>
    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> Back
    </a>
  </div>
  <div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Department Information</h5></div>
    <div class="card-body">
      <form action="{{ route('departments.update', $department) }}" method="POST">
        @csrf
        @method('PUT')
        @include('departments.partials.form', ['submitLabel' => 'Update Department'])
      </form>
    </div>
  </div>
</div>
@endsection
