@extends('layouts/layoutMaster')

@section('title', 'CheckList Group Details')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header Section -->
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1">{{ $checklistGroup->code }}</h4>
          <div class="d-flex align-items-center gap-2">
            @if($checklistGroup->quality_procedure_reference)
              <span class="badge bg-label-info">{{ $checklistGroup->quality_procedure_reference }}</span>
            @endif
            @if($checklistGroup->department)
              <span class="badge bg-label-secondary">{{ $checklistGroup->department }}</span>
            @endif
            <span class="badge bg-label-{{ $checklistGroup->is_active ? 'success' : 'secondary' }}">
              {{ $checklistGroup->is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('checklist-groups.index') }}" class="btn btn-sm btn-secondary">
            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
          </a>
          <a href="{{ route('checklist-groups.edit', $checklistGroup) }}" class="btn btn-sm btn-primary">
            <i class="icon-base ti tabler-edit me-1"></i> Edit
          </a>
          <form action="{{ route('checklist-groups.destroy', $checklistGroup) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"
              onclick="return confirm('Are you sure you want to delete this checklist group?')">
              <i class="icon-base ti tabler-trash me-1"></i> Delete
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Group Details and Properties -->
    <div class="row">
      <div class="col-md-8">
        <!-- Group Details Card -->
        <div class="card mb-6">
          <div class="card-header">
            <h5 class="mb-0">Group Details</h5>
          </div>
          <div class="card-body">
            <div class="mb-4">
              <h6 class="text-muted mb-2">Title</h6>
              <p class="mb-0">{{ $checklistGroup->title }}</p>
            </div>

            @if($checklistGroup->description)
            <div class="mb-4">
              <h6 class="text-muted mb-2">Description</h6>
              <p class="mb-0">{{ $checklistGroup->description }}</p>
            </div>
            @endif

            <div class="alert alert-info mb-0">
              <h6 class="alert-heading mb-2">
                <i class="icon-base ti tabler-info-circle me-1"></i> CheckList Group Purpose
              </h6>
              <p class="mb-0">
                This checklist group organizes related audit questions for systematic audit execution.
                Questions in this group will be presented together during audits, allowing auditors to verify compliance
                and identify non-conformances systematically.
              </p>
            </div>
          </div>
        </div>

        <!-- Audit Questions Card -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="icon-base ti tabler-list-check me-1"></i>
              Audit Questions ({{ $checklistGroup->auditQuestions->count() }})
            </h5>
          </div>
          <div class="card-body">
            @forelse($checklistGroup->auditQuestions as $question)
              <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="badge bg-label-primary me-2">{{ $question->code }}</span>
                    <span class="badge bg-label-{{ $question->category_color }}">{{ $question->category_label }}</span>
                    @if($question->is_required)
                      <span class="badge bg-label-danger ms-2">Required</span>
                    @endif
                  </div>
                  <a href="{{ route('audit-questions.show', $question) }}" class="btn btn-sm btn-label-primary">
                    <i class="icon-base ti tabler-eye"></i>
                  </a>
                </div>
                <p class="mb-0">{{ $question->question }}</p>
                @if($question->description)
                  <small class="text-muted">{{ $question->description }}</small>
                @endif
              </div>
            @empty
              <div class="text-center py-4">
                <i class="icon-base ti tabler-list-check icon-60px text-muted mb-3"></i>
                <h6 class="text-muted">No questions in this group</h6>
                <p class="text-muted">Add audit questions to this checklist group to use it in audits</p>
                <a href="{{ route('checklist-groups.edit', $checklistGroup) }}" class="btn btn-sm btn-primary">
                  <i class="icon-base ti tabler-edit me-1"></i> Add Questions
                </a>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <!-- Properties Card -->
        <div class="card mb-6">
          <div class="card-header">
            <h5 class="mb-0">Properties</h5>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td class="text-nowrap fw-medium">Code</td>
                  <td><span class="badge bg-label-primary">{{ $checklistGroup->code }}</span></td>
                </tr>
                @if($checklistGroup->quality_procedure_reference)
                <tr>
                  <td class="text-nowrap fw-medium">QP Reference</td>
                  <td><span class="badge bg-label-info">{{ $checklistGroup->quality_procedure_reference }}</span></td>
                </tr>
                @endif
                @if($checklistGroup->department)
                <tr>
                  <td class="text-nowrap fw-medium">Department</td>
                  <td><span class="badge bg-label-secondary">{{ $checklistGroup->department }}</span></td>
                </tr>
                @endif
                <tr>
                  <td class="text-nowrap fw-medium">Display Order</td>
                  <td>{{ $checklistGroup->display_order }}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Active</td>
                  <td>
                    <span class="badge bg-label-{{ $checklistGroup->is_active ? 'success' : 'secondary' }}">
                      {{ $checklistGroup->is_active ? 'Yes' : 'No' }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Questions</td>
                  <td>
                    <span class="badge bg-label-primary">{{ $checklistGroup->auditQuestions->count() }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Timestamps Card -->
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Timestamps</h5>
          </div>
          <div class="card-body">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td class="text-nowrap fw-medium">Created</td>
                  <td>{{ $checklistGroup->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fw-medium">Last Updated</td>
                  <td>{{ $checklistGroup->updated_at->format('d M Y, H:i') }}</td>
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
