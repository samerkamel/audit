@extends('layouts/layoutMaster')

@section('title', 'CheckList Groups')

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
                <span class="text-heading">Total Groups</span>
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  <i class="icon-base ti tabler-list icon-26px"></i>
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
                <span class="text-heading">Active Groups</span>
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
                <span class="text-heading">With Questions</span>
                <h4 class="mb-0 me-2">{{ $stats['with_questions'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-info">
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
                <span class="text-heading">Departments</span>
                <h4 class="mb-0 me-2">{{ $stats['departments'] }}</h4>
              </div>
              <div class="avatar">
                <span class="avatar-initial rounded-circle bg-label-warning">
                  <i class="icon-base ti tabler-building icon-26px"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CheckList Groups Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">CheckList Groups</h5>
        <a href="{{ route('checklist-groups.create') }}" class="btn btn-primary">
          <i class="icon-base ti tabler-plus me-1"></i> Add Group
        </a>
      </div>

      <!-- Filters -->
      <div class="card-body">
        <form method="GET" action="{{ route('checklist-groups.index') }}" class="mb-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Search by code, title, QP reference..."
                value="{{ request('search') }}">
            </div>

            <div class="col-md-3">
              <label class="form-label">Department</label>
              <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                  <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                    {{ $department }}
                  </option>
                @endforeach
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
              <a href="{{ route('checklist-groups.index') }}" class="btn btn-label-secondary">
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
              <th>Title</th>
              <th>QP Reference</th>
              <th>Department</th>
              <th>Questions</th>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($checklistGroups as $group)
            <tr>
              <td>
                <span class="fw-medium">{{ $group->code }}</span>
              </td>
              <td>
                <div style="max-width: 300px;">
                  {{ Str::limit($group->title, 60) }}
                </div>
              </td>
              <td>
                @if($group->quality_procedure_reference)
                  <span class="badge bg-label-info">{{ $group->quality_procedure_reference }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                @if($group->department)
                  <span class="badge bg-label-secondary">{{ $group->department }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <span class="badge bg-label-primary">{{ $group->auditQuestions->count() }} questions</span>
              </td>
              <td>
                <span class="badge bg-label-{{ $group->is_active ? 'success' : 'secondary' }}">
                  {{ $group->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>{{ $group->display_order }}</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base ti tabler-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('checklist-groups.show', $group) }}">
                      <i class="icon-base ti tabler-eye me-1"></i> View
                    </a>
                    <a class="dropdown-item" href="{{ route('checklist-groups.edit', $group) }}">
                      <i class="icon-base ti tabler-edit me-1"></i> Edit
                    </a>
                    <form action="{{ route('checklist-groups.destroy', $group) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger"
                        onclick="return confirm('Are you sure you want to delete this group?')">
                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                      </button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                  <i class="icon-base ti tabler-list icon-60px text-muted mb-3"></i>
                  <h5 class="text-muted">No checklist groups found</h5>
                  <p class="text-muted">Start by creating your first checklist group</p>
                  <a href="{{ route('checklist-groups.create') }}" class="btn btn-primary mt-2">
                    <i class="icon-base ti tabler-plus me-1"></i> Add Group
                  </a>
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      @if($checklistGroups->hasPages())
      <div class="card-footer">
        {{ $checklistGroups->links() }}
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
