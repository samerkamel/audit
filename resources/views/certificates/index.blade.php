@extends('layouts/layoutMaster')

@section('title', __('ISO Certificates'))

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
            <h4 class="fw-bold mb-1">{{ __('ISO Certificates') }}</h4>
            <p class="text-muted mb-0">{{ __('Manage certification documents and validity tracking') }}</p>
        </div>
        <div class="d-flex gap-2">
            <!-- Export Dropdown -->
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon-base ti tabler-download me-1"></i> {{ __('Export') }}
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.certificates.pdf') }}" target="_blank">
                            <i class="icon-base ti tabler-file-type-pdf me-2 text-danger"></i>{{ __('Export to PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('reports.certificates.excel') }}">
                            <i class="icon-base ti tabler-file-spreadsheet me-2 text-success"></i>{{ __('Export to Excel') }}
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('certificates.create') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-circle-plus me-1"></i>{{ __('Register New Certificate') }}
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-6 mb-6">
        <!-- Total Certificates -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Total') }}</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-2">
                            <i class="icon-base ti tabler-award ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valid Certificates -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Valid') }}</p>
                            <h3 class="mb-0 text-success">{{ $stats['valid'] }}</h3>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-2">
                            <i class="icon-base ti tabler-circle-check ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Expiring Soon') }}</p>
                            <h3 class="mb-0 text-warning">{{ $stats['expiring_soon'] }}</h3>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-2">
                            <i class="icon-base ti tabler-alert-triangle ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expired -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Expired') }}</p>
                            <h3 class="mb-0 text-danger">{{ $stats['expired'] }}</h3>
                        </div>
                        <span class="badge bg-label-danger rounded-pill p-2">
                            <i class="icon-base ti tabler-x ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspended -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Suspended') }}</p>
                            <h3 class="mb-0 text-secondary">{{ $stats['suspended'] }}</h3>
                        </div>
                        <span class="badge bg-label-secondary rounded-pill p-2">
                            <i class="icon-base ti tabler-player-pause ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revoked -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('Revoked') }}</p>
                            <h3 class="mb-0 text-dark">{{ $stats['revoked'] }}</h3>
                        </div>
                        <span class="badge bg-label-dark rounded-pill p-2">
                            <i class="icon-base ti tabler-slash ti-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Certificate Registry') }}</h5>
        </div>
        <div class="card-datatable table-responsive">
                <table id="certificatesTable" class="datatables-certificate table dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ __('Certificate Number') }}</th>
                            <th>{{ __('Standard') }}</th>
                            <th>{{ __('Certification Body') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Issue Date') }}</th>
                            <th>{{ __('Expiry Date') }}</th>
                            <th>{{ __('Validity') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Audit') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $certificate)
                        <tr class="{{ $certificate->isExpiringSoon() ? 'table-warning' : '' }} {{ $certificate->isExpired() ? 'table-danger' : '' }}">
                            <td>
                                <a href="{{ route('certificates.show', $certificate) }}" class="text-decoration-none fw-semibold">
                                    {{ $certificate->certificate_number }}
                                </a>
                            </td>
                            <td>{{ $certificate->standard }}</td>
                            <td>{{ $certificate->certification_body }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $certificate->certificate_type_label }}
                                </span>
                            </td>
                            <td>{{ $certificate->issue_date->format('M d, Y') }}</td>
                            <td>
                                {{ $certificate->expiry_date->format('M d, Y') }}
                                @if($certificate->isExpiringSoon())
                                <br><small class="text-warning">
                                    <i class="icon-base ti tabler-clock ti-xs"></i> {{ $certificate->days_until_expiry }} {{ __('days left') }}
                                </small>
                                @elseif($certificate->isExpired())
                                <br><small class="text-danger">
                                    <i class="icon-base ti tabler-alert-triangle ti-xs"></i> {{ __('Expired') }}
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($certificate->validity_period_in_years)
                                {{ $certificate->validity_period_in_years }} {{ __('year(s)') }}
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $certificate->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($certificate->issuedForAudit)
                                <a href="{{ route('external-audits.show', $certificate->issuedForAudit) }}" class="text-decoration-none">
                                    {{ $certificate->issuedForAudit->audit_number }}
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('certificates.show', $certificate) }}"
                                       class="btn btn-outline-primary"
                                       title="{{ __('View Details') }}">
                                        <i class="icon-base ti tabler-eye"></i>
                                    </a>

                                    @if(in_array($certificate->status, ['valid', 'expiring_soon']))
                                    <a href="{{ route('certificates.edit', $certificate) }}"
                                       class="btn btn-outline-secondary"
                                       title="{{ __('Edit') }}">
                                        <i class="icon-base ti tabler-edit"></i>
                                    </a>
                                    @endif

                                    @if(in_array($certificate->status, ['expired', 'revoked']))
                                    <form action="{{ route('certificates.destroy', $certificate) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this certificate?') }}')">
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
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="icon-base ti tabler-folder-off ti-xl d-block mb-2 opacity-50"></i>
                                {{ __('No certificates registered yet') }}.
                                <a href="{{ route('certificates.create') }}">{{ __('Register your first certificate') }}</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#certificatesTable').DataTable({
        responsive: true,
        order: [[5, 'asc']], // Sort by expiry date ascending (soonest first)
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [9] }, // Disable sorting on actions column
            { responsivePriority: 1, targets: 0 }, // Certificate Number always visible
            { responsivePriority: 2, targets: -1 } // Actions always visible
        ],
        language: {
            search: "{{ __('Search certificates...') }}",
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
