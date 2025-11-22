@extends('layouts/layoutMaster')

@section('title', __('View Message'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">{{ __('Messages') }}</a></li>
          <li class="breadcrumb-item active">{{ __('View') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-0">{{ $message->subject }}</h4>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
        <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back') }}
      </a>
      <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('{{ __('Delete this message?') }}')">
          <i class="icon-base ti tabler-trash me-1"></i> {{ __('Delete') }}
        </button>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <!-- Message Thread -->
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-label-{{ $message->priority_color }}">{{ ucfirst($message->priority) }}</span>
              @if($message->related_type_label)
              <span class="badge bg-label-info ms-1">
                <i class="icon-base ti tabler-link me-1"></i>{{ $message->related_type_label }}
                @if($message->related)
                  - <a href="{{ $message->related_type === 'App\Models\AuditPlan' ? route('audit-plans.show', $message->related_id) : '#' }}" class="text-white">View</a>
                @endif
              </span>
              @endif
            </div>
            <small class="text-muted">{{ $thread->count() }} {{ __('message(s) in thread') }}</small>
          </div>
        </div>
        <div class="card-body p-0">
          @foreach($thread as $msg)
          <div class="p-4 border-bottom {{ $msg->sender_id === auth()->id() ? 'bg-light-subtle' : '' }}">
            <div class="d-flex align-items-start">
              <div class="avatar me-3">
                <span class="avatar-initial rounded-circle bg-label-{{ $msg->sender_id === auth()->id() ? 'primary' : 'secondary' }}">
                  {{ substr($msg->sender->name, 0, 2) }}
                </span>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <h6 class="mb-0">
                      {{ $msg->sender->name }}
                      @if($msg->sender_id === auth()->id())
                      <span class="badge bg-label-primary ms-1">{{ __('You') }}</span>
                      @endif
                    </h6>
                    <small class="text-muted">{{ __('To:') }} {{ $msg->recipient->name }}</small>
                  </div>
                  <small class="text-muted">{{ $msg->created_at->format('M d, Y H:i') }}</small>
                </div>
                <div class="message-body">
                  {!! nl2br(e($msg->body)) !!}
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Reply Form -->
      <div class="card">
        <div class="card-header border-bottom">
          <h5 class="card-title mb-0">{{ __('Reply') }}</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('messages.reply', $message) }}" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">{{ __('Your Reply') }}</label>
              <textarea class="form-control @error('body') is-invalid @enderror" name="body" rows="5" required placeholder="{{ __('Type your reply...') }}">{{ old('body') }}</textarea>
              @error('body')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-send me-1"></i> {{ __('Send Reply') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<script>
  window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: '{{ __('Success!') }}',
      text: '{{ session('success') }}',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  });
</script>
@endif
@endsection
