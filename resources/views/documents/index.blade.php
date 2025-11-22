@extends('layouts/layoutMaster')

@section('title', 'Document Management')

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
            <h4 class="fw-bold mb-1">Document Management</h4>
            <p class="text-muted mb-0">ISO 9001:2015 Document Control System</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Export Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon-base ti tabler-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.documents.pdf') }}" target="_blank">
                            <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>Export to PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.documents.excel') }}">
                            <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>Export to Excel
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-file-plus me-1"></i>Create Document
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-6 mb-6">
        <!-- Total Documents -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-2">
                            <i class="icon-base ti tabler-file-text ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Effective Documents -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Effective</p>
                            <h3 class="mb-0 text-success">{{ $stats['effective'] }}</h3>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-2">
                            <i class="icon-base ti tabler-circle-check ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pending Review</p>
                            <h3 class="mb-0 text-info">{{ $stats['pending_review'] }}</h3>
                        </div>
                        <span class="badge bg-label-info rounded-pill p-2">
                            <i class="icon-base ti tabler-eye ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approval -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pending Approval</p>
                            <h3 class="mb-0 text-warning">{{ $stats['pending_approval'] }}</h3>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-2">
                            <i class="icon-base ti tabler-clock ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Needs Review -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Needs Review</p>
                            <h3 class="mb-0 text-danger">{{ $stats['needs_review'] }}</h3>
                        </div>
                        <span class="badge bg-label-danger rounded-pill p-2">
                            <i class="icon-base ti tabler-alert-triangle ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Obsolete -->
        <div class="col-sm-6 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Obsolete</p>
                            <h3 class="mb-0 text-secondary">{{ $stats['obsolete'] }}</h3>
                        </div>
                        <span class="badge bg-label-secondary rounded-pill p-2">
                            <i class="icon-base ti tabler-archive ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Document Registry</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table id="documentsTable" class="datatables-document table dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Document Number</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Effective Date</th>
                        <th>Next Review</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                    <tr class="{{ $document->needsReview() ? 'table-warning' : '' }}">
                        <td>
                            <a href="{{ route('documents.show', $document) }}" class="text-decoration-none fw-semibold">
                                {{ $document->document_number }}
                            </a>
                        </td>
                        <td>{{ $document->title }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                {{ $document->category_label }}
                            </span>
                        </td>
                        <td>{{ $document->version }}</td>
                        <td>
                            <span class="badge bg-{{ $document->status_color }}">
                                {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                            </span>
                        </td>
                        <td>{{ $document->owner?->name ?? '-' }}</td>
                        <td>
                            @if($document->effective_date)
                            {{ $document->effective_date->format('M d, Y') }}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($document->next_review_date)
                            {{ $document->next_review_date->format('M d, Y') }}
                            @if($document->needsReview())
                            <br><small class="text-danger">
                                <i class="icon-base ti tabler-alert-triangle ti-xs"></i> Review overdue
                            </small>
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('documents.show', $document) }}"
                                   class="btn btn-outline-primary"
                                   title="View Details">
                                    <i class="icon-base ti tabler-eye"></i>
                                </a>

                                @if($document->file_path)
                                <a href="{{ route('documents.download', $document) }}"
                                   class="btn btn-outline-success"
                                   title="Download">
                                    <i class="icon-base ti tabler-download"></i>
                                </a>
                                @endif

                                @if($document->canBeEdited())
                                <a href="{{ route('documents.edit', $document) }}"
                                   class="btn btn-outline-secondary"
                                   title="Edit">
                                    <i class="icon-base ti tabler-edit"></i>
                                </a>
                                @endif

                                @if(!$document->isEffective())
                                <form action="{{ route('documents.destroy', $document) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="icon-base ti tabler-folder-off ti-xl d-block mb-2 opacity-50"></i>
                            No documents found.
                            <a href="{{ route('documents.create') }}">Create your first document</a>
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
    $('#documentsTable').DataTable({
        responsive: true,
        order: [[0, 'desc']], // Sort by document number descending
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [8] }, // Disable sorting on actions column
            { responsivePriority: 1, targets: 0 }, // Document Number always visible
            { responsivePriority: 2, targets: -1 } // Actions always visible
        ],
        language: {
            search: "Search documents:",
            lengthMenu: "Show _MENU_ documents per page",
            info: "Showing _START_ to _END_ of _TOTAL_ documents",
            infoEmpty: "No documents available",
            infoFiltered: "(filtered from _MAX_ total documents)"
        }
    });
});
</script>
@endpush
@endsection
