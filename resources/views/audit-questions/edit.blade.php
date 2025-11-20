@extends('layouts/layoutMaster')

@section('title', 'Edit Audit Question')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Audit Question</h5>
        <a href="{{ route('audit-questions.index') }}" class="btn btn-sm btn-secondary">
          <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('audit-questions.update', $auditQuestion) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row">
            <!-- Question Code -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="code">Question Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code"
                value="{{ old('code', $auditQuestion->code) }}" placeholder="e.g., COMP-001" required>
              @error('code')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Unique identifier for this question</small>
            </div>

            <!-- Category -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
              <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="compliance" {{ old('category', $auditQuestion->category) === 'compliance' ? 'selected' : '' }}>Compliance</option>
                <option value="operational" {{ old('category', $auditQuestion->category) === 'operational' ? 'selected' : '' }}>Operational</option>
                <option value="financial" {{ old('category', $auditQuestion->category) === 'financial' ? 'selected' : '' }}>Financial</option>
                <option value="it" {{ old('category', $auditQuestion->category) === 'it' ? 'selected' : '' }}>IT</option>
                <option value="quality" {{ old('category', $auditQuestion->category) === 'quality' ? 'selected' : '' }}>Quality</option>
                <option value="security" {{ old('category', $auditQuestion->category) === 'security' ? 'selected' : '' }}>Security</option>
              </select>
              @error('category')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Question Text -->
            <div class="col-12 mb-3">
              <label class="form-label" for="question">Question <span class="text-danger">*</span></label>
              <textarea class="form-control @error('question') is-invalid @enderror" id="question" name="question"
                rows="3" placeholder="Enter the audit question..." required>{{ old('question', $auditQuestion->question) }}</textarea>
              @error('question')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Description -->
            <div class="col-12 mb-3">
              <label class="form-label" for="description">Description</label>
              <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                name="description" rows="3"
                placeholder="Additional context or guidance for this question...">{{ old('description', $auditQuestion->description) }}</textarea>
              @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Provide additional context or guidance for auditors</small>
            </div>

            <!-- Display Order -->
            <div class="col-md-4 mb-3">
              <label class="form-label" for="display_order">Display Order</label>
              <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order"
                name="display_order" value="{{ old('display_order', $auditQuestion->display_order) }}" min="0">
              @error('display_order')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Lower numbers appear first</small>
            </div>

            <!-- Is Required -->
            <div class="col-md-4 mb-3">
              <label class="form-label d-block">Required Question</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1"
                  {{ old('is_required', $auditQuestion->is_required) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_required">
                  This question must be answered
                </label>
              </div>
              <small class="text-muted">Required questions must be answered in all audits</small>
            </div>

            <!-- Is Active -->
            <div class="col-md-4 mb-3">
              <label class="form-label d-block">Active Status</label>
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                  {{ old('is_active', $auditQuestion->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                  Question is active
                </label>
              </div>
              <small class="text-muted">Only active questions can be used in audits</small>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-3">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Update Question
              </button>
              <a href="{{ route('audit-questions.index') }}" class="btn btn-label-secondary">
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
