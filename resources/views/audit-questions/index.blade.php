@extends('layouts/layoutMaster')

@section('title', 'Audit Questions')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Statistics Cards -->
    <div class="row g-6 mb-6">
      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">Total Questions</span>
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  <i class="icon-base ti tabler-list-check icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">Active Questions</span>
                <h4 class="mb-0 me-2">{{ $stats['active'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-success">
                  <i class="icon-base ti tabler-check icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">Required Questions</span>
                <h4 class="mb-0 me-2">{{ $stats['required'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-danger">
                  <i class="icon-base ti tabler-alert-circle icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div class="content-left">
                <span class="text-heading">Categories</span>
                <h4 class="mb-0 me-2">{{ count($stats['by_category']) }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-info">
                  <i class="icon-base ti tabler-category icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Questions Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Audit Questions</h5>
        <a href="{{ route('audit-questions.create') }}" class="btn btn-primary">
          <i class="icon-base ti tabler-plus me-1"></i> Add Question
        </a>
      </div>

      <!-- Filters -->
      <div class="card-body">
        <form method="GET" action="{{ route('audit-questions.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Search by code, question..."
                value="{{ request('search') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Category</label>
              <select name="category" class="form-select">
                <option value="">All Categories</option>
                <option value="compliance" {{ request('category') === 'compliance' ? 'selected' : '' }}>Compliance</option>
                <option value="operational" {{ request('category') === 'operational' ? 'selected' : '' }}>Operational</option>
                <option value="financial" {{ request('category') === 'financial' ? 'selected' : '' }}>Financial</option>
                <option value="it" {{ request('category') === 'it' ? 'selected' : '' }}>IT</option>
                <option value="quality" {{ request('category') === 'quality' ? 'selected' : '' }}>Quality</option>
                <option value="security" {{ request('category') === 'security' ? 'selected' : '' }}>Security</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select name="is_active" class="form-select">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary me-2">
                <i class="icon-base ti tabler-search me-1"></i> Filter
              </button>
              <a href="{{ route('audit-questions.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-x"></i>
              </a>
            </div>
          </div>
        </form>
      </div>

      <!-- Table -->
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Question</th>
              <th>Category</th>
              <th>Status</th>
              <th>Required</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($auditQuestions as $question)
            <tr>
              <td>
                <span class="fw-medium">{{ $question->code }}</span>
              </td>
              <td>
                <div style="max-width: 400px;">
                  {{ Str::limit($question->question, 80) }}
                </div>
              </td>
              <td>
                <span class="badge bg-label-{{ $question->category_color }}">
                  {{ $question->category_label }}
                </span>
              </td>
              <td>
                <span class="badge bg-label-{{ $question->is_active ? 'success' : 'secondary' }}">
                  {{ $question->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                @if($question->is_required)
                  <span class="badge bg-label-danger">
                    <i class="icon-base ti tabler-alert-circle"></i> Required
                  </span>
                @else
                  <span class="badge bg-label-secondary">Optional</span>
                @endif
              </td>
              <td>{{ $question->display_order }}</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('audit-questions.show', $question) }}">
                      <i class="icon-base ti tabler-eye me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="{{ route('audit-questions.edit', $question) }}">
                      <i class="icon-base ti tabler-edit me-1"></i> Edit
                    </a>
                    <form action="{{ route('audit-questions.destroy', $question) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger"
                        onclick="return confirm('Are you sure you want to delete this question?')">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                      </button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                  <i class="icon-base ti tabler-list-check icon-60px text-muted mb-3"></i>
                  <h5 class="text-muted">No audit questions found</h5>
                  <p class="text-muted">Start by creating your first audit question</p>
                  <a href="{{ route('audit-questions.create') }}" class="btn btn-primary mt-2">
                    <i class="icon-base ti tabler-plus me-1"></i> Add Question
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($auditQuestions->hasPages())
      <div class="card-footer">
        {{ $auditQuestions->links() }}
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
