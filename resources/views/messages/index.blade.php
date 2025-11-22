@extends('layouts/layoutMaster')

@section('title', 'Messages')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <h4 class="fw-bold mb-1">Messages</h4>
      <p class="text-muted mb-0">Internal messaging system</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('messages.sent') }}" class="btn btn-outline-secondary">
        <i class="icon-base ti tabler-send me-1"></i> Sent Messages
      </a>
      <a href="{{ route('messages.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> Compose Message
      </a>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Inbox</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['total'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-primary">
                <i class="icon-base ti tabler-inbox icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Unread</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['unread'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-warning">
                <i class="icon-base ti tabler-mail icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading">Sent</span>
              <div class="d-flex align-items-center my-1">
                <h4 class="mb-0 me-2">{{ $statistics['sent'] }}</h4>
              </div>
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded-circle bg-label-success">
                <i class="icon-base ti tabler-send icon-26px"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('messages.index') }}" method="GET" class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" name="search" placeholder="Search messages..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" name="priority">
            <option value="">All Priority</option>
            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Messages List Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center border-bottom">
      <h5 class="card-title mb-0">Inbox</h5>
      @if($statistics['unread'] > 0)
      <form action="{{ route('messages.mark-all-read') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-primary">
          <i class="icon-base ti tabler-checks me-1"></i> Mark All as Read
        </button>
      </form>
      @endif
    </div>
    <div class="card-body p-0">
      @forelse($messages as $message)
      <a href="{{ route('messages.show', $message) }}" class="d-block text-body border-bottom p-3 {{ !$message->isRead() ? 'bg-light' : '' }}">
        <div class="d-flex align-items-start">
          <div class="avatar me-3">
            <span class="avatar-initial rounded-circle bg-label-{{ $message->priority_color }}">
              {{ substr($message->sender->name, 0, 2) }}
            </span>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h6 class="mb-0 {{ !$message->isRead() ? 'fw-bold' : '' }}">{{ $message->sender->name }}</h6>
              <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 {{ !$message->isRead() ? 'fw-semibold' : '' }}">
              {{ $message->subject }}
              @if($message->replies->count() > 0)
              <span class="badge bg-label-secondary ms-1">{{ $message->replies->count() }} replies</span>
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
            @if(!$message->isRead())
            <span class="badge bg-primary ms-1">New</span>
            @endif
          </div>
        </div>
      </a>
      @empty
      <div class="text-center py-5">
        <i class="icon-base ti tabler-inbox icon-48px text-muted mb-3"></i>
        <p class="text-muted mb-0">No messages in your inbox</p>
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

@if(session('success'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ session('success') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif
@endsection
