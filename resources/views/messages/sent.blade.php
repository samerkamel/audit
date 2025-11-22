@extends('layouts/layoutMaster')

@section('title', __('Sent Messages'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Sent Messages') }}</h4>
      <p class="text-muted mb-0">{{ __('Messages you have sent') }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
        <i class="icon-base ti tabler-inbox me-1"></i> {{ __('Inbox') }}
      </a>
      <a href="{{ route('messages.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> {{ __('Compose Message') }}
      </a>
    </div>
  </div>

  <!-- Search Bar -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('messages.sent') }}" method="GET" class="row g-3">
        <div class="col-md-10">
          <input type="text" class="form-control" name="search" placeholder="{{ __('Search sent messages...') }}" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">{{ __('Search') }}</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Messages List Card -->
  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ __('Sent Messages') }}</h5>
    </div>
    <div class="card-body p-0">
      @forelse($messages as $message)
      <a href="{{ route('messages.show', $message) }}" class="d-block text-body border-bottom p-3">
        <div class="d-flex align-items-start">
          <div class="avatar me-3">
            <span class="avatar-initial rounded-circle bg-label-{{ $message->priority_color }}">
              {{ substr($message->recipient->name, 0, 2) }}
            </span>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h6 class="mb-0">{{ __('To:') }} {{ $message->recipient->name }}</h6>
              <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1">
              {{ $message->subject }}
              @if($message->replies->count() > 0)
              <span class="badge bg-label-secondary ms-1">{{ $message->replies->count() }} {{ __('replies') }}</span>
              @endif
            </p>
            <p class="text-muted mb-0 small">{{ Str::limit(strip_tags($message->body), 100) }}</p>
            @if($message->related_type_label)
            <span class="badge bg-label-info mt-1">
              <i class="icon-base ti tabler-link me-1"></i>{{ $message->related_type_label }}
            </span>
            @endif
          </div>
          <div class="d-flex align-items-center ms-2">
            <span class="badge bg-label-{{ $message->priority_color }}">{{ ucfirst($message->priority) }}</span>
            @if($message->isRead())
            <span class="badge bg-label-success ms-1" title="{{ __('Read') }}"><i class="icon-base ti tabler-checks"></i></span>
            @endif
          </div>
        </div>
      </a>
      @empty
      <div class="text-center py-5">
        <i class="icon-base ti tabler-send icon-48px text-muted mb-3"></i>
        <p class="text-muted mb-0">{{ __('No sent messages') }}</p>
      </div>
      @endforelse
    </div>
    @if($messages->hasPages())
    <div class="card-footer">
      {{ $messages->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
