@extends('layouts/layoutMaster')

@section('title', 'Audit Question Details')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header Section -->
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1">{{ $auditQuestion->code }}</h4>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-{{ $auditQuestion->category_color }}">
              {{ $auditQuestion->category_label }}
            </span>
            <span class="badge bg-label-{{ $auditQuestion->is_active ? 'success' : 'secondary' }}">
              {{ $auditQuestion->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if($auditQuestion->is_required)
              <span class="badge bg-label-danger">
                <i class="icon-base ti tabler-alert-circle"></i> Required
              </span>
            @endif
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('audit-questions.index') }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
          </a>
          <a href="{{ route('audit-questions.edit', $auditQuestion) }}" class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-edit me-1"></i> Edit
          </a>
          <form action="{{ route('audit-questions.destroy', $auditQuestion) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
              onclick="return confirm('Are you sure you want to delete this question?')">
              <i class="icon-base ti tabler-trash me-1"></i> Delete
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Question Details -->
    <div class="row">
      <div class="col-md-8">
        <div class="card mb-6">
          <div class="card-header">
            <h5 class="mb-0">Question Details</h5>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <h6 class="text-muted mb-2">Question</h6>
              <p class="mb-0">{{ $auditQuestion->question }}</p>
            </div>

            @if($auditQuestion->description)
            <div class="mb-4">
              <h6 class="text-muted mb-2">Description</h6>
              <p class="mb-0">{{ $auditQuestion->description }}</p>
            </div>
            @endif

            <div class="alert alert-info mb-0">
              <h6 class="alert-heading mb-2">
                <i class="icon-base ti tabler-info-circle me-1"></i> Compliance-Based Question
              </h6>
              <p class="mb-0">
                This question is answered with compliance status (complied/not complied) rather than text.
                Auditors can add comments to provide additional context for their compliance assessment.
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card mb-6">
          <div class="card-header">
            <h5 class="mb-0">Properties</h5>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td class="text-nowrap fw-medium">Code</td>
                  <td><span class="badge bg-label-primary">{{ $auditQuestion->code }}</span></td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Category</td>
                  <td>
                    <span class="badge bg-label-{{ $auditQuestion->category_color }}">
                      {{ $auditQuestion->category_label }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Display Order</td>
                  <td>{{ $auditQuestion->display_order }}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Required</td>
                  <td>
                    @if($auditQuestion->is_required)
                      <span class="badge bg-label-danger">Yes</span>
                    @else
                      <span class="badge bg-label-secondary">No</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Active</td>
                  <td>
                    <span class="badge bg-label-{{ $auditQuestion->is_active ? 'success' : 'secondary' }}">
                      {{ $auditQuestion->is_active ? 'Yes' : 'No' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Timestamps</h5>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td class="text-nowrap fw-medium">Created</td>
                  <td>{{ $auditQuestion->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Last Updated</td>
                  <td>{{ $auditQuestion->updated_at->format('d M Y, H:i') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
