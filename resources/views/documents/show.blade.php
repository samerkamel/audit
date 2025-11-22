@extends('layouts/layoutMaster')

@section('title', __('Document Details'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">{{ __('Documents') }}</a></li>
                <li class="breadcrumb-item active">{{ $document->document_number }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h3 mb-1">{{ $document->document_number }}</h1>
                <p class="text-muted mb-0">
                    {{ $document->title }}
                </p>
            </div>
            <div>
                <span class="badge bg-{{ $document->status_color }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Review Warning Alert -->
    @if($document->needsReview())
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="icon-base ti tabler-alert-triangle fs-4 me-3"></i>
        <div>
            <strong>{{ __('Review Required!') }}</strong> {{ __('This document is due for review.') }}
            {{ __('Next review date was') }} {{ $document->next_review_date->format('F d, Y') }}.
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Document Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Document Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Document Number') }}</label>
                            <p class="mb-0 fw-semibold">{{ $document->document_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Category') }}</label>
                            <p class="mb-0">{{ $document->category_label }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Version') }}</label>
                            <p class="mb-0">{{ $document->version }} ({{ __('Revision') }} {{ $document->revision_number }})</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Status') }}</label>
                            <p class="mb-0">
                                <span class="badge bg-{{ $document->status_color }}">
                                    {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted small">{{ __('Description') }}</label>
                            <p class="mb-0">{{ $document->description ?? __('No description provided') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Version & Dates -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Version Control') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($document->effective_date)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Effective Date') }}</label>
                            <p class="mb-0">{{ $document->effective_date->format('F d, Y') }}</p>
                        </div>
                        @endif
                        @if($document->next_review_date)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Next Review Date') }}</label>
                            <p class="mb-0">
                                {{ $document->next_review_date->format('F d, Y') }}
                                @if($document->days_until_review !== null)
                                    @if($document->days_until_review > 0)
                                    <br><small class="text-muted">{{ __(':days days remaining', ['days' => $document->days_until_review]) }}</small>
                                    @else
                                    <br><small class="text-danger">{{ __('Overdue by :days days', ['days' => abs($document->days_until_review)]) }}</small>
                                    @endif
                                @endif
                            </p>
                        </div>
                        @endif
                        @if($document->supersedes)
                        <div class="col-md-12">
                            <label class="text-muted small">{{ __('Supersedes') }}</label>
                            <p class="mb-0">
                                <a href="{{ route('documents.show', $document->supersedes) }}">
                                    {{ $document->supersedes->document_number }} - {{ $document->supersedes->title }}
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($document->revision_notes)
                        <div class="col-md-12">
                            <label class="text-muted small">{{ __('Revision Notes') }}</label>
                            <p class="mb-0">{{ $document->revision_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- File Information -->
            @if($document->file_path)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Attached File') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 fw-semibold">
                                <i class="icon-base ti tabler-file-text me-1"></i>
                                {{ $document->file_name }}
                            </p>
                            <p class="mb-0 small text-muted">
                                {{ $document->file_size_formatted }} • {{ strtoupper($document->file_type) }}
                            </p>
                        </div>
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                            <i class="icon-base ti tabler-download me-1"></i>{{ __('Download') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Ownership & Approval -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Ownership & Approval') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Document Owner') }}</label>
                            <p class="mb-0">{{ $document->owner->name ?? __('Not assigned') }}</p>
                        </div>
                        @if($document->reviewed_by)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Reviewed By') }}</label>
                            <p class="mb-0">
                                {{ $document->reviewer->name }}<br>
                                <small class="text-muted">{{ $document->reviewed_date->format('M d, Y') }}</small>
                            </p>
                        </div>
                        @endif
                        @if($document->approved_by)
                        <div class="col-md-6">
                            <label class="text-muted small">{{ __('Approved By') }}</label>
                            <p class="mb-0">
                                {{ $document->approver->name }}<br>
                                <small class="text-muted">{{ $document->approved_date->format('M d, Y') }}</small>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Applicable Information -->
            @if($document->applicable_departments || $document->keywords)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Applicability & Keywords') }}</h5>
                </div>
                <div class="card-body">
                    @if($document->applicable_departments && count($document->applicable_departments) > 0)
                    <div class="mb-3">
                        <label class="text-muted small">{{ __('Applicable Departments') }}</label>
                        <p class="mb-0">
                            @foreach($departments->whereIn('id', $document->applicable_departments) as $dept)
                            <span class="badge bg-label-primary me-1">{{ $dept->name }}</span>
                            @endforeach
                        </p>
                    </div>
                    @endif

                    @if($document->keywords && count($document->keywords) > 0)
                    <div>
                        <label class="text-muted small">{{ __('Keywords') }}</label>
                        <p class="mb-0">
                            @foreach($document->keywords as $keyword)
                            <span class="badge bg-label-secondary me-1">{{ $keyword }}</span>
                            @endforeach
                        </p>
                    </div>
                    @endif
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
                    @can('update', $document)
                    <!-- Edit Document -->
                    @if($document->canBeEdited())
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="icon-base ti tabler-edit me-1"></i>{{ __('Edit Document') }}
                    </a>
                    @endif

                    <!-- Submit for Review -->
                    @if($document->isDraft())
                    <form action="{{ route('documents.submit-for-review', $document) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="icon-base ti tabler-send me-1"></i>{{ __('Submit for Review') }}
                        </button>
                    </form>
                    @endif

                    <!-- Review -->
                    @if($document->canBeReviewed())
                    <form action="{{ route('documents.review', $document) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="icon-base ti tabler-clipboard-check me-1"></i>{{ __('Mark as Reviewed') }}
                        </button>
                    </form>
                    @endif

                    <!-- Approve -->
                    @if($document->canBeApproved())
                    <form action="{{ route('documents.approve', $document) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="icon-base ti tabler-circle-check me-1"></i>{{ __('Approve Document') }}
                        </button>
                    </form>
                    @endif

                    <!-- Make Effective -->
                    @if($document->status === 'approved')
                    <form action="{{ route('documents.make-effective', $document) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to make this document effective? This will set the effective date and schedule the next review.') }}')">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="icon-base ti tabler-checkup-list me-1"></i>{{ __('Make Effective') }}
                        </button>
                    </form>
                    @endif

                    <!-- Make Obsolete -->
                    @if($document->isEffective())
                    <form action="{{ route('documents.make-obsolete', $document) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('Are you sure you want to mark this document as obsolete?') }}')">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                            <i class="icon-base ti tabler-ban me-1"></i>{{ __('Mark as Obsolete') }}
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>

            <!-- Related Documents -->
            @if($document->supersededBy->count() > 0)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Superseded By') }}</h5>
                </div>
                <div class="card-body">
                    @foreach($document->supersededBy as $newer)
                    <div class="mb-2">
                        <a href="{{ route('documents.show', $newer) }}">
                            {{ $newer->document_number }}
                        </a>
                        <br>
                        <small class="text-muted">{{ $newer->title }}</small>
                        <span class="badge bg-{{ $newer->status_color }} badge-sm">
                            {{ ucfirst($newer->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Metadata -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">{{ __('Details') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <strong>{{ __('Created by:') }}</strong><br>
                        {{ $document->createdBy->name ?? __('System') }}<br>
                        <span class="text-muted">{{ $document->created_at->format('M d, Y') }}</span>
                    </p>
                    @if($document->updated_by)
                    <p class="mb-0 small">
                        <strong>{{ __('Last updated by:') }}</strong><br>
                        {{ $document->updatedBy->name }}<br>
                        <span class="text-muted">{{ $document->updated_at->diffForHumans() }}</span>
                    </p>
                    @else
                    <p class="mb-0 small">
                        <strong>{{ __('Last updated:') }}</strong><br>
                        <span class="text-muted">{{ $document->updated_at->diffForHumans() }}</span>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
