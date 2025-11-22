@extends('layouts/layoutMaster')

@section('title', __('Document Management'))

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
            <h4 class="fw-bold mb-1">{{ __('Document Management') }}</h4>
            <p class="text-muted mb-0">{{ __('ISO 9001:2015 Document Control System') }}</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Export Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon-base ti tabler-download me-1"></i> {{ __('Export') }}
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.documents.pdf') }}" target="_blank">
                            <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>{{ __('Export to PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.documents.excel') }}">
                            <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>{{ __('Export to Excel') }}
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-file-plus me-1"></i>{{ __('Create Document') }}
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
                            <p class="text-muted mb-1 small">{{ __('Total') }}</p>
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
                            <p class="text-muted mb-1 small">{{ __('Effective') }}</p>
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
                            <p class="text-muted mb-1 small">{{ __('Pending Review') }}</p>
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
                            <p class="text-muted mb-1 small">{{ __('Pending Approval') }}</p>
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
                            <p class="text-muted mb-1 small">{{ __('Needs Review') }}</p>
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
                            <p class="text-muted mb-1 small">{{ __('Obsolete') }}</p>
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
            <h5 class="card-title mb-0">{{ __('Documents List') }}</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table id="documentsTable" class="datatables-document table dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Owner') }}</th>
                        <th>{{ __('Issue Date') }}</th>
                        <th>{{ __('Actions') }}</th>
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
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('documents.show', $document) }}"
                                   class="btn btn-outline-primary"
                                   title="{{ __('View Details') }}">
                                    <i class="icon-base ti tabler-eye"></i>
                                </a>

                                @if($document->file_path)
                                <a href="{{ route('documents.download', $document) }}"
                                   class="btn btn-outline-success"
                                   title="{{ __('Download') }}">
                                    <i class="icon-base ti tabler-download"></i>
                                </a>
                                @endif

                                @if($document->canBeEdited())
                                <a href="{{ route('documents.edit', $document) }}"
                                   class="btn btn-outline-secondary"
                                   title="{{ __('Edit') }}">
                                    <i class="icon-base ti tabler-edit"></i>
                                </a>
                                @endif

                                @if(!$document->isEffective())
                                <form action="{{ route('documents.destroy', $document) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="icon-base ti tabler-folder-off ti-xl d-block mb-2 opacity-50"></i>
                            {{ __('No data found') }}.
                            <a href="{{ route('documents.create') }}">{{ __('Create Document') }}</a>
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
            { orderable: false, targets: [7] }, // Disable sorting on actions column
            { responsivePriority: 1, targets: 0 }, // Document Number always visible
            { responsivePriority: 2, targets: -1 } // Actions always visible
        ],
        language: {
            search: "",
            searchPlaceholder: "{{ __('Search') }}...",
            lengthMenu: "{{ __('Show') }} _MENU_",
            info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_",
            infoEmpty: "{{ __('No data found') }}",
            infoFiltered: ""
        }
    });
});
</script>
@endpush
@endsection
