@extends('layouts/layoutMaster')

@section('title', 'External Audits')

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
      <h4 class="fw-bold mb-1">External Audits</h4>
      <p class="text-muted mb-0">Manage external certification and surveillance audits</p>
    </div>
    <div class="d-flex gap-2">
      <!-- Export Dropdown -->
      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="icon-base ti tabler-download me-1"></i> Export
        </button>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item" href="{{ route('reports.audits.pdf') }}" target="_blank">
              <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>Export to PDF
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('reports.audits.excel') }}">
              <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>Export to Excel
            </a>
          </li>
        </ul>
      </div>
      <a href="{{ route('external-audits.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> Schedule New Audit
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
              <span class="text-heading">Total Audits</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['total'] }}</h4>
              </div>
              <small class="mb-0">All time</small>
            </div>
            <span class="badge bg-label-primary rounded-pill p-2">
              <i class="icon-base ti tabler-clipboard-check ti-lg"></i>
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
              <span class="text-heading">Scheduled</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['scheduled'] }}</h4>
              </div>
              @if($stats['upcoming'] > 0)
              <small class="text-warning mb-0">{{ $stats['upcoming'] }} upcoming</small>
              @else
              <small class="mb-0">Future audits</small>
              @endif
            </div>
            <span class="badge bg-label-info rounded-pill p-2">
              <i class="icon-base ti tabler-calendar-event ti-lg"></i>
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
              <span class="text-heading">In Progress</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['in_progress'] }}</h4>
              </div>
              <small class="mb-0">Currently active</small>
            </div>
            <span class="badge bg-label-warning rounded-pill p-2">
              <i class="icon-base ti tabler-clock-hour-4 ti-lg"></i>
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
              <span class="text-heading">Completed</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['completed'] }}</h4>
              </div>
              @if($stats['passed'] > 0)
              <small class="text-success mb-0">{{ $stats['passed'] }} passed</small>
              @else
              <small class="mb-0">Finished audits</small>
              @endif
            </div>
            <span class="badge bg-label-success rounded-pill p-2">
              <i class="icon-base ti tabler-circle-check ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Additional Statistics Row -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Certificates Issued</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $stats['with_certificate'] }}</h4>
              </div>
              @if($stats['completed'] > 0)
              <small class="mb-0">{{ round(($stats['with_certificate'] / $stats['completed']) * 100) }}% of completed</small>
              @else
              <small class="mb-0">From audits</small>
              @endif
            </div>
            <span class="badge bg-label-success rounded-pill p-2">
              <i class="icon-base ti tabler-award ti-lg"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Audits Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Audit History</h5>
    </div>
    <div class="card-datatable table-responsive">
      <table id="auditsTable" class="datatables-audit table dt-responsive nowrap" style="width:100%">
        <thead>
          <tr>
            <th>Audit Number</th>
            <th>Type</th>
            <th>Standard</th>
            <th>Certification Body</th>
            <th>Lead Auditor</th>
            <th>Scheduled Date</th>
            <th>Status</th>
            <th>Result</th>
            <th>Certificate</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($audits as $audit)
          <tr>
            <td>
              <a href="{{ route('external-audits.show', $audit) }}" class="text-heading fw-medium">
                {{ $audit->audit_number }}
              </a>
            </td>
            <td><span class="badge bg-label-secondary">{{ $audit->audit_type_label }}</span></td>
            <td>{{ $audit->standard }}</td>
            <td>{{ $audit->certification_body }}</td>
            <td>
              {{ $audit->lead_auditor_name }}
              @if($audit->lead_auditor_email)
              <br><small class="text-muted">{{ $audit->lead_auditor_email }}</small>
              @endif
            </td>
            <td>
              {{ $audit->scheduled_start_date->format('M d, Y') }}
              @if($audit->isUpcoming())
              <br><small class="text-warning"><i class="icon-base ti tabler-clock ti-xs"></i> Upcoming</small>
              @elseif($audit->isOverdue())
              <br><small class="text-danger"><i class="icon-base ti tabler-alert-triangle ti-xs"></i> Overdue</small>
              @endif
            </td>
            <td><span class="badge bg-label-{{ $audit->status_color }}">{{ ucfirst(str_replace('_', ' ', $audit->status)) }}</span></td>
            <td><span class="badge bg-label-{{ $audit->result_color }}">{{ ucfirst($audit->result) }}</span></td>
            <td>
              @if($audit->certificate)
              <a href="{{ route('certificates.show', $audit->certificate) }}" class="text-success">
                <i class="icon-base ti tabler-award"></i> View
              </a>
              @elseif($audit->canGenerateCertificate())
              <a href="{{ route('certificates.create', ['audit' => $audit->id]) }}" class="text-primary">
                <i class="icon-base ti tabler-plus"></i> Generate
              </a>
              @else
              <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <a href="{{ route('external-audits.show', $audit) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="View Details">
                  <i class="icon-base ti tabler-eye ti-md"></i>
                </a>
                @if(in_array($audit->status, ['scheduled', 'in_progress']))
                <a href="{{ route('external-audits.edit', $audit) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Edit">
                  <i class="icon-base ti tabler-edit ti-md"></i>
                </a>
                @endif
                @if($audit->status === 'scheduled')
                <form action="{{ route('external-audits.destroy', $audit) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this audit?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Delete">
                    <i class="icon-base ti tabler-trash ti-md"></i>
                  </button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-4">
              <i class="icon-base ti tabler-folder-off ti-xl d-block mb-2 opacity-50"></i>
              No external audits scheduled yet.
              <br><a href="{{ route('external-audits.create') }}">Schedule your first audit</a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
  $('#auditsTable').DataTable({
    responsive: true,
    order: [[5, 'desc']], // Sort by scheduled date descending
    pageLength: 25,
    columnDefs: [
      { orderable: false, targets: [9] }, // Disable sorting on actions column
      { responsivePriority: 1, targets: 0 }, // Audit Number always visible
      { responsivePriority: 2, targets: -1 } // Actions always visible
    ],
    language: {
      search: "Search audits:",
      lengthMenu: "Show _MENU_ audits",
      info: "Showing _START_ to _END_ of _TOTAL_ audits",
      infoEmpty: "No audits available",
      infoFiltered: "(filtered from _MAX_ total audits)"
    }
  });
});
</script>
@endpush
@endsection
