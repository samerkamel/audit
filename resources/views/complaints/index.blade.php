@extends('layouts/layoutMaster')

@section('title', 'Customer Complaints')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Customer Complaints</h4>
      <p class="text-muted mb-0">Manage and track customer feedback and complaints</p>
    </div>
    <div class="d-flex gap-2">
      <!-- Export Dropdown -->
      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="icon-base ti tabler-download me-1"></i> Export
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="{{ route('reports.complaints.pdf') }}" target="_blank">
              <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>Export to PDF
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('reports.complaints.excel') }}">
              <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>Export to Excel
            </a>
          </li>
        </ul>
      </div>
      <a href="{{ route('complaints.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> New Complaint
      </a>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Total Complaints</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <small class="mb-0">All time</small>
            </div>
            <span class="badge bg-label-primary rounded-pill p-2">
              <i class="icon-base ti tabler-file-description ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">New Complaints</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['new'] }}</h4>
              </div>
              <small class="mb-0">Requires acknowledgment</small>
            </div>
            <span class="badge bg-label-info rounded-pill p-2">
              <i class="icon-base ti tabler-file-plus ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Under Investigation</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['investigating'] }}</h4>
              </div>
              <small class="mb-0">In progress</small>
            </div>
            <span class="badge bg-label-warning rounded-pill p-2">
              <i class="icon-base ti tabler-search ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Resolved</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['resolved'] }}</h4>
              </div>
              <small class="mb-0">Awaiting closure</small>
            </div>
            <span class="badge bg-label-success rounded-pill p-2">
              <i class="icon-base ti tabler-circle-check ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Closed</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['closed'] }}</h4>
              </div>
              <small class="mb-0">Successfully completed</small>
            </div>
            <span class="badge bg-label-secondary rounded-pill p-2">
              <i class="icon-base ti tabler-lock ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Overdue</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['overdue'] }}</h4>
              </div>
              <small class="mb-0">Past response date</small>
            </div>
            <span class="badge bg-label-danger rounded-pill p-2">
              <i class="icon-base ti tabler-alert-triangle ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">High Priority</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['high_priority'] }}</h4>
              </div>
              <small class="mb-0">Critical & High</small>
            </div>
            <span class="badge bg-label-danger rounded-pill p-2">
              <i class="icon-base ti tabler-alert-circle ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">CAR Generated</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['car_generated'] }}</h4>
              </div>
              <small class="mb-0">With corrective actions</small>
            </div>
            <span class="badge bg-label-primary rounded-pill p-2">
              <i class="icon-base ti tabler-file-arrow-right ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Complaints Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">All Complaints</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-complaints table table-hover dt-responsive nowrap" style="width:100%">
        <thead>
          <tr>
            <th>Complaint #</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Subject</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>CAR</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($complaints as $complaint)
          <tr class="{{ $complaint->isOverdue() ? 'table-danger' : '' }}">
            <td>
              <a href="{{ route('complaints.show', $complaint) }}" class="fw-medium">
                {{ $complaint->complaint_number }}
              </a>
            </td>
            <td>
              <span class="text-nowrap">{{ $complaint->complaint_date->format('M d, Y') }}</span>
            </td>
            <td>
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $complaint->customer_name }}</span>
                @if($complaint->customer_company)
                <small class="text-muted">{{ $complaint->customer_company }}</small>
                @endif
              </div>
            </td>
            <td>
              <div style="max-width: 250px;">
                <span class="text-truncate d-block" title="{{ $complaint->complaint_subject }}">
                  {{ $complaint->complaint_subject }}
                </span>
              </div>
            </td>
            <td>
              <span class="badge bg-label-secondary">{{ $complaint->category_label }}</span>
            </td>
            <td>
              <span class="badge bg-label-{{ $complaint->priority_color }}">
                {{ ucfirst($complaint->priority) }}
              </span>
            </td>
            <td>
              <span class="badge bg-label-{{ $complaint->severity_color }}">
                {{ ucfirst($complaint->severity) }}
              </span>
            </td>
            <td>
              <span class="badge bg-label-{{ $complaint->status_color }}">
                {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
              </span>
              @if($complaint->isOverdue())
              <span class="badge bg-label-danger ms-1">Overdue</span>
              @endif
            </td>
            <td>
              @if($complaint->assignedToUser)
              <div class="d-flex flex-column">
                <span class="fw-medium">{{ $complaint->assignedToUser->name }}</span>
                @if($complaint->assignedToDepartment)
                <small class="text-muted">{{ $complaint->assignedToDepartment->name }}</small>
                @endif
              </div>
              @elseif($complaint->assignedToDepartment)
              <span class="text-muted">{{ $complaint->assignedToDepartment->name }}</span>
              @else
              <span class="text-muted">Unassigned</span>
              @endif
            </td>
            <td>
              @if($complaint->car)
              <a href="{{ route('cars.show', $complaint->car) }}" class="text-primary">
                {{ $complaint->car->car_number }}
              </a>
              @else
              <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="icon-base ti tabler-dots-vertical ti-md"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('complaints.show', $complaint) }}">
                      <i class="icon-base ti tabler-eye me-2"></i>
                      <span>View Details</span>
                    </a>
                  </li>
                  @if(in_array($complaint->status, ['new', 'acknowledged']))
                  <li>
                    <a class="dropdown-item" href="{{ route('complaints.edit', $complaint) }}">
                      <i class="icon-base ti tabler-edit me-2"></i>
                      <span>Edit</span>
                    </a>
                  </li>
                  @endif
                  @if($complaint->status === 'new')
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li>
                    <form action="{{ route('complaints.destroy', $complaint) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this complaint?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger">
                        <i class="icon-base ti tabler-trash me-2"></i>
                        <span>Delete</span>
                      </button>
                    </form>
                  </li>
                  @endif
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize DataTable
  $('.datatables-complaints').DataTable({
    responsive: true,
    order: [[1, 'desc']], // Sort by date descending
    pageLength: 25,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    columnDefs: [
      { responsivePriority: 1, targets: 0 }, // Complaint Number always visible
      { responsivePriority: 2, targets: -1 } // Actions always visible
    ],
    language: {
      search: '',
      searchPlaceholder: 'Search complaints...',
      lengthMenu: '_MENU_',
      paginate: {
        next: '<i class="icon-base ti tabler-chevron-right ti-sm"></i>',
        previous: '<i class="icon-base ti tabler-chevron-left ti-sm"></i>'
      }
    }
  });
});
</script>
@endsection
