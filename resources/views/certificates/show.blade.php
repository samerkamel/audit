@extends('layouts/layoutMaster')

@section('title', __('Certificate Details'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('certificates.index') }}">{{ __('Certificates') }}</a></li>
                <li class="breadcrumb-item active">{{ $certificate->certificate_number }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3 mb-1">{{ $certificate->certificate_number }}</h1>
                <p class="text-muted mb-0">
                    {{ $certificate->standard }} - {{ $certificate->certification_body }}
                </p>
            </div>
            <div>
                <span class="badge bg-{{ $certificate->status_color }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Expiry Warning Alert -->
    @if($certificate->isExpiringSoon())
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="icon-base ti tabler-alert-triangle fs-4 me-3"></i>
        <div>
            <strong>{{ __('Expiry Warning!') }}</strong> {{ __('This certificate expires in') }} <strong>{{ $certificate->days_until_expiry }} {{ __('days') }}</strong> {{ __('on') }} {{ $certificate->expiry_date->format('F d, Y') }}.
            {{ __('Please arrange for recertification audit.') }}
        </div>
    </div>
    @endif

    @if($certificate->isExpired())
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="icon-base ti tabler-x fs-4 me-3"></i>
        <div>
            <strong>{{ __('Certificate Expired!') }}</strong> {{ __('This certificate expired on') }} {{ $certificate->expiry_date->format('F d, Y') }}.
            {{ __('Immediate action required to maintain certification status.') }}
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Certificate Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Certificate Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Certificate Number') }}</label>
                            <p class="mb-0 fw-semibold">{{ $certificate->certificate_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Certificate Type') }}</label>
                            <p class="mb-0">{{ $certificate->certificate_type_label }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Standard') }}</label>
                            <p class="mb-0">{{ $certificate->standard }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Certification Body') }}</label>
                            <p class="mb-0">{{ $certificate->certification_body }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validity Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Validity Period') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('Issue Date') }}</label>
                            <p class="mb-0">{{ $certificate->issue_date->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('Expiry Date') }}</label>
                            <p class="mb-0">{{ $certificate->expiry_date->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">{{ __('Validity Period') }}</label>
                            <p class="mb-0">{{ $certificate->validity_period_in_years }} {{ __('year(s)') }}</p>
                        </div>
                        @if(!$certificate->isExpired())
                        <div class="col-md-12">
                            <div class="progress" style="height: 25px;">
                                @php
                                $totalDays = $certificate->issue_date->diffInDays($certificate->expiry_date);
                                $daysElapsed = $certificate->issue_date->diffInDays(now());
                                $percentElapsed = min(($daysElapsed / $totalDays) * 100, 100);
                                @endphp
                                <div class="progress-bar {{ $percentElapsed > 80 ? 'bg-danger' : ($percentElapsed > 60 ? 'bg-warning' : 'bg-success') }}"
                                     role="progressbar"
                                     style="width: {{ $percentElapsed }}%"
                                     aria-valuenow="{{ $percentElapsed }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    {{ round($percentElapsed) }}% {{ __('of validity period used') }}
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $certificate->days_until_expiry > 0 ? $certificate->days_until_expiry . ' ' . __('days remaining') : __('Expired') }}
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scope of Certification -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Scope of Certification') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="mb-0">{{ $certificate->scope_of_certification }}</p>
                    </div>

                    @if($certificate->covered_sites && count($certificate->covered_sites) > 0)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Covered Sites') }}</label>
                        <ul class="mb-0">
                            @foreach($certificate->covered_sites as $site)
                            <li>{{ $site }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if($certificate->covered_processes && count($certificate->covered_processes) > 0)
                    <div>
                        <label class="text-muted small">{{ __('Covered Processes') }}</label>
                        <ul class="mb-0">
                            @foreach($certificate->covered_processes as $process)
                            <li>{{ $process }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Additional Notes -->
            @if($certificate->notes)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Notes') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $certificate->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Actions') }}</h5>
                </div>
                <div class="card-body">
                    @can('update', $certificate)
                    <!-- Edit Certificate -->
                    @if(in_array($certificate->status, ['valid', 'expiring_soon']))
                    <a href="{{ route('certificates.edit', $certificate) }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="icon-base ti tabler-edit me-1"></i>{{ __('Edit Details') }}
                    </a>
                    @endif

                    <!-- Suspend Certificate -->
                    @if($certificate->status !== 'suspended' && $certificate->status !== 'revoked' && $certificate->status !== 'expired')
                    <form action="{{ route('certificates.suspend', $certificate) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to suspend this certificate?') }}')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                            <i class="icon-base ti tabler-player-pause me-1"></i>{{ __('Suspend Certificate') }}
                        </button>
                    </form>
                    @endif

                    <!-- Revoke Certificate -->
                    @if($certificate->status !== 'revoked')
                    <form action="{{ route('certificates.revoke', $certificate) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to revoke this certificate? This action indicates the certificate is permanently invalidated.') }}')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                            <i class="icon-base ti tabler-slash me-1"></i>{{ __('Revoke Certificate') }}
                        </button>
                    </form>
                    @endif

                    <!-- Reinstate Certificate -->
                    @if($certificate->status === 'suspended')
                    <form action="{{ route('certificates.reinstate', $certificate) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="icon-base ti tabler-circle-check me-1"></i>{{ __('Reinstate Certificate') }}
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>

            <!-- Related Audit -->
            @if($certificate->issuedForAudit)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Related Audit') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ $certificate->issuedForAudit->audit_number }}</strong>
                    </p>
                    <p class="mb-2">
                        <span class="badge bg-{{ $certificate->issuedForAudit->status_color }}">
                            {{ ucfirst(str_replace('_', ' ', $certificate->issuedForAudit->status)) }}
                        </span>
                        <span class="badge bg-{{ $certificate->issuedForAudit->result_color }}">
                            {{ ucfirst($certificate->issuedForAudit->result) }}
                        </span>
                    </p>
                    <p class="mb-2 small">
                        <strong>{{ __('Type') }}:</strong> {{ $certificate->issuedForAudit->audit_type_label }}<br>
                        <strong>{{ __('Date') }}:</strong> {{ $certificate->issuedForAudit->actual_end_date->format('M d, Y') }}
                    </p>
                    <a href="{{ route('external-audits.show', $certificate->issuedForAudit) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="icon-base ti tabler-eye me-1"></i>{{ __('View Audit Details') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Status Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Status Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Current Status') }}</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $certificate->status_color }}">
                                {{ ucfirst(str_replace('_', ' ', $certificate->status)) }}
                            </span>
                        </p>
                    </div>

                    @if(!$certificate->isExpired())
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Validity Check') }}</label>
                        <p class="mb-0">
                            @if($certificate->isValid())
                            <i class="icon-base ti tabler-circle-check text-success"></i> {{ __('Certificate is valid') }}
                            @elseif($certificate->isExpiringSoon())
                            <i class="icon-base ti tabler-alert-triangle text-warning"></i> {{ __('Expiring in') }} {{ $certificate->days_until_expiry }} {{ __('days') }}
                            @else
                            <i class="icon-base ti tabler-x text-danger"></i> {{ __('Not currently valid') }}
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Details') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <strong>{{ __('Created by') }}:</strong><br>
                        {{ $certificate->createdBy->name ?? __('System') }}<br>
                        <span class="text-muted">{{ $certificate->created_at->format('M d, Y') }}</span>
                    </p>
                    <p class="mb-0 small">
                        <strong>{{ __('Last updated') }}:</strong><br>
                        <span class="text-muted">{{ $certificate->updated_at->diffForHumans() }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
