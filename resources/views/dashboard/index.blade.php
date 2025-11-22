@extends('layouts/layoutMaster')

@section('title', 'Dashboard - ISO 9001:2015 Audit Management')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/apex-charts/apexcharts.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="fw-bold mb-1">Quality Management Dashboard</h4>
            <p class="text-muted mb-0">ISO 9001:2015 Audit Management System Overview</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-download me-1"></i>Export Report
            </button>
            <button type="button" class="btn btn-primary">
                <i class="icon-base ti tabler-refresh me-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Critical Alerts -->
    @if($stats['overdue_cars'] > 0 || $stats['expired_certificates'] > 0 || $stats['documents_needing_review'] > 0)
    <div class="row g-3 mb-6">
        @if($stats['overdue_cars'] > 0)
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="icon-base ti tabler-alert-triangle fs-4 me-3"></i>
                <div>
                    <strong>{{ $stats['overdue_cars'] }} Overdue CAR(s)</strong> - Immediate attention required
                    <a href="{{ route('cars.index') }}" class="alert-link ms-2">View CARs</a>
                </div>
            </div>
        </div>
        @endif

        @if($stats['expired_certificates'] > 0)
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="icon-base ti tabler-certificate fs-4 me-3"></i>
                <div>
                    <strong>{{ $stats['expired_certificates'] }} Expired Certificate(s)</strong> - Recertification required
                    <a href="{{ route('certificates.index') }}" class="alert-link ms-2">View Certificates</a>
                </div>
            </div>
        </div>
        @endif

        @if($stats['documents_needing_review'] > 0)
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="icon-base ti tabler-file-alert fs-4 me-3"></i>
                <div>
                    <strong>{{ $stats['documents_needing_review'] }} Document(s) Need Review</strong>
                    <a href="{{ route('documents.index') }}" class="alert-link ms-2">Review Documents</a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Main Statistics Grid -->
    <div class="row g-6 mb-6">
        <!-- Audits Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Total Audits</p>
                            <h3 class="mb-0">{{ $stats['total_audit_plans'] }}</h3>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-2">
                            <i class="icon-base ti tabler-clipboard-check ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <small class="text-success">
                            <i class="icon-base ti tabler-check ti-xs"></i>
                            {{ $stats['completed_audits'] }} completed
                        </small>
                        <small class="text-warning">
                            <i class="icon-base ti tabler-clock ti-xs"></i>
                            {{ $stats['pending_audits'] }} pending
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARs Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Active CARs</p>
                            <h3 class="mb-0 {{ $stats['open_cars'] > 0 ? 'text-danger' : '' }}">{{ $stats['open_cars'] }}</h3>
                        </div>
                        <span class="badge bg-label-danger rounded-pill p-2">
                            <i class="icon-base ti tabler-alert-circle ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <small class="text-muted">
                            {{ $stats['total_cars'] }} total
                        </small>
                        @if($stats['overdue_cars'] > 0)
                        <small class="text-danger">
                            <i class="icon-base ti tabler-alert-triangle ti-xs"></i>
                            {{ $stats['overdue_cars'] }} overdue
                        </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Complaints Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Open Complaints</p>
                            <h3 class="mb-0 {{ $stats['unresolved_complaints'] > 0 ? 'text-warning' : '' }}">{{ $stats['unresolved_complaints'] }}</h3>
                        </div>
                        <span class="badge bg-label-warning rounded-pill p-2">
                            <i class="icon-base ti tabler-message-report ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <small class="text-muted">
                            {{ $stats['total_complaints'] }} total
                        </small>
                        <small class="text-success">
                            {{ $stats['total_complaints'] - $stats['unresolved_complaints'] }} resolved
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Effective Documents</p>
                            <h3 class="mb-0 text-primary">{{ $stats['effective_documents'] }}</h3>
                        </div>
                        <span class="badge bg-label-info rounded-pill p-2">
                            <i class="icon-base ti tabler-file-text ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <small class="text-muted">
                            {{ $stats['total_documents'] }} total
                        </small>
                        @if($stats['pending_review_documents'] > 0)
                        <small class="text-info">
                            {{ $stats['pending_review_documents'] }} in review
                        </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificates Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Valid Certificates</p>
                            <h3 class="mb-0 text-success">{{ $stats['valid_certificates'] }}</h3>
                        </div>
                        <span class="badge bg-label-success rounded-pill p-2">
                            <i class="icon-base ti tabler-award ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        @if($stats['expiring_certificates'] > 0)
                        <small class="text-warning">
                            <i class="icon-base ti tabler-clock ti-xs"></i>
                            {{ $stats['expiring_certificates'] }} expiring
                        </small>
                        @endif
                        @if($stats['expired_certificates'] > 0)
                        <small class="text-danger">
                            {{ $stats['expired_certificates'] }} expired
                        </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- External Audits Statistics -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">External Audits</p>
                            <h3 class="mb-0">{{ $stats['total_external_audits'] }}</h3>
                        </div>
                        <span class="badge bg-label-primary rounded-pill p-2">
                            <i class="icon-base ti tabler-file-certificate ti-lg"></i>
                        </span>
                    </div>
                    <div class="d-flex gap-3">
                        <small class="text-info">
                            {{ $stats['scheduled_external_audits'] }} scheduled
                        </small>
                        <small class="text-warning">
                            {{ $stats['upcoming_external_audits'] }} upcoming
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities - Full Width -->
    <div class="row g-6 mb-6">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activities</h5>
                    <a href="#" class="btn btn-sm btn-text-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td style="width: 50px;">
                                        <span class="badge bg-label-{{ $activity['color'] }} rounded-pill p-2">
                                            <i class="icon-base ti tabler-{{ $activity['icon'] }}"></i>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ $activity['url'] }}" class="fw-semibold text-dark">
                                                {{ $activity['title'] }}
                                            </a>
                                            <p class="text-muted mb-0 small">{{ \Illuminate\Support\Str::limit($activity['description'], 60) }}</p>
                                        </div>
                                    </td>
                                    <td class="text-end" style="width: 150px;">
                                        <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="icon-base ti tabler-inbox ti-xl d-block mb-2 opacity-50"></i>
                                        No recent activities
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trends Chart - Full Width -->
    <div class="row g-6 mb-6">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Audit Completion Trends (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <div id="auditTrendsChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Widgets - 4 Column Grid -->
    <div class="row g-6">
        <!-- Upcoming External Audits -->
        @if($upcomingExternalAudits->count() > 0)
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Upcoming External Audits</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($upcomingExternalAudits as $audit)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <a href="{{ route('external-audits.show', $audit) }}" class="fw-semibold">
                                {{ $audit->audit_number }}
                            </a>
                            <p class="mb-0 small text-muted">{{ $audit->standard }}</p>
                            <small class="text-primary">
                                <i class="icon-base ti tabler-calendar ti-xs"></i>
                                {{ $audit->scheduled_start_date?->format('M d, Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <span class="badge bg-label-info">
                            {{ $audit->scheduled_start_date?->diffInDays(now()) ?? 0 }}d
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Expiring Certificates -->
        @if($expiringCertificates->count() > 0)
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Expiring Certificates</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($expiringCertificates as $cert)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <a href="{{ route('certificates.show', $cert) }}" class="fw-semibold">
                                {{ $cert->certificate_number }}
                            </a>
                            <p class="mb-0 small text-muted">{{ $cert->standard }}</p>
                            <small class="text-warning">
                                <i class="icon-base ti tabler-clock ti-xs"></i>
                                {{ $cert->expiry_date?->format('M d, Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <span class="badge bg-label-warning">
                            {{ $cert->days_until_expiry }}d
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Overdue CARs -->
        @if($overdueCars->count() > 0)
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Overdue CARs</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($overdueCars as $car)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <a href="{{ route('cars.show', $car) }}" class="fw-semibold">
                                {{ $car->car_number }}
                            </a>
                            <p class="mb-0 small text-muted">{{ \Illuminate\Support\Str::limit($car->description, 40) }}</p>
                            <small class="text-danger">
                                <i class="icon-base ti tabler-alert-triangle ti-xs"></i>
                                Due: {{ $car->due_date?->format('M d, Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <span class="badge bg-label-danger">
                            {{ $car->due_date ? abs($car->due_date->diffInDays(now())) : 0 }}d overdue
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Documents Needing Review -->
        @if($documentsNeedingReview->count() > 0)
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Documents Needing Review</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($documentsNeedingReview as $doc)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <a href="{{ route('documents.show', $doc) }}" class="fw-semibold">
                                {{ $doc->document_number }}
                            </a>
                            <p class="mb-0 small text-muted">{{ \Illuminate\Support\Str::limit($doc->title, 40) }}</p>
                            <small class="text-warning">
                                <i class="icon-base ti tabler-calendar ti-xs"></i>
                                Review: {{ $doc->next_review_date?->format('M d, Y') ?? 'N/A' }}
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Audit Trends Chart
    const auditTrendsData = @json($auditTrends);
    const months = Object.keys(auditTrendsData);
    const counts = Object.values(auditTrendsData);

    const auditTrendsOptions = {
        series: [{
            name: 'Completed Audits',
            data: counts
        }],
        chart: {
            height: 300,
            type: 'area',
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1
            }
        },
        colors: ['#696cff'],
        xaxis: {
            categories: months,
            labels: {
                formatter: function(value) {
                    if (!value) return '';
                    const date = new Date(value + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return Math.round(value);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return value + ' audits';
                }
            }
        }
    };

    const auditTrendsChart = new ApexCharts(
        document.querySelector("#auditTrendsChart"),
        auditTrendsOptions
    );
    auditTrendsChart.render();
});
</script>
@endpush
@endsection
