@extends('layouts/layoutMaster')

@section('title', 'Edit CheckList Group')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit CheckList Group</h5>
        <a href="{{ route('checklist-groups.index') }}" class="btn btn-sm btn-secondary">
          <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('checklist-groups.update', $checklistGroup) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row">
            <!-- Group Code -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="code">Group Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                value="{{ old('code', $checklistGroup->code) }}" placeholder="e.g., CLG-001" required>
              @error('code')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Unique identifier for this checklist group</small>
            </div>

            <!-- Quality Procedure Reference -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="quality_procedure_reference">Quality Procedure Reference</label>
              <input type="text" class="form-control @error('quality_procedure_reference') is-invalid @enderror"
                id="quality_procedure_reference" name="quality_procedure_reference"
                value="{{ old('quality_procedure_reference', $checklistGroup->quality_procedure_reference) }}"
                placeholder="e.g., QP-03-01">
              @error('quality_procedure_reference')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">ISO quality procedure reference code</small>
            </div>

            <!-- Title -->
            <div class="col-12 mb-3">
              <label class="form-label" for="title">Group Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                value="{{ old('title', $checklistGroup->title) }}" placeholder="Enter the group title..." required>
              @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Description -->
            <div class="col-12 mb-3">
              <label class="form-label" for="description">Description</label>
              <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                name="description" rows="3"
                placeholder="Describe the purpose of this checklist group...">{{ old('description', $checklistGroup->description) }}</textarea>
              @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Provide context about what this group covers</small>
            </div>

            <!-- Department -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="department">Department</label>
              <input type="text" class="form-control @error('department') is-invalid @enderror" id="department"
                name="department" value="{{ old('department', $checklistGroup->department) }}"
                placeholder="e.g., Commercial, General">
              @error('department')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Department this group applies to (optional)</small>
            </div>

            <!-- Display Order -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="display_order">Display Order</label>
              <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order"
                name="display_order" value="{{ old('display_order', $checklistGroup->display_order) }}" min="0">
              @error('display_order')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Lower numbers appear first</small>
            </div>

            <!-- Is Active -->
            <div class="col-12 mb-3">
              <label class="form-label d-block">Active Status</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                  {{ old('is_active', $checklistGroup->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  Group is active
                </label>
              </div>
              <small class="text-muted">Only active groups can be used in audits</small>
            </div>

            <!-- Questions Selection -->
            <div class="col-12 mb-3">
              <label class="form-label">Audit Questions</label>
              <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                @forelse($auditQuestions as $question)
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="questions[]" value="{{ $question->id }}"
                      id="question_{{ $question->id }}"
                      {{ in_array($question->id, old('questions', $selectedQuestions)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="question_{{ $question->id }}">
                      <span class="badge bg-label-primary me-2">{{ $question->code }}</span>
                      <span class="badge bg-label-{{ $question->category_color }} me-2">{{ $question->category_label }}</span>
                      {{ $question->question }}
                      @if($question->is_required)
                        <span class="badge bg-label-danger ms-2">Required</span>
                      @endif
                    </label>
                  </div>
                @empty
                  <p class="text-muted mb-0">No audit questions available. Please create audit questions first.</p>
                @endforelse
              </div>
              @error('questions')
              <div class="text-danger mt-1">{{ $message }}</div>
              @enderror
              <small class="text-muted">Select questions to include in this checklist group</small>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-3">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Update Group
              </button>
              <a href="{{ route('checklist-groups.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x me-1"></i> Cancel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
