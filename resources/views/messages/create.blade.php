@extends('layouts/layoutMaster')

@section('title', __('Compose Message'))

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex justify-content-between align-items-center mb-6">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">{{ __('Messages') }}</a></li>
          <li class="breadcrumb-item active">{{ __('Compose') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-0">{{ __('Compose New Message') }}</h4>
    </div>
    <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
      <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back to Inbox') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('messages.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
          <div class="col-md-8">
            <label class="form-label">{{ __('Recipient') }} <span class="text-danger">*</span></label>
            <select class="form-select select2 @error('recipient_id') is-invalid @enderror" name="recipient_id" required>
              <option value="">{{ __('Select Recipient') }}</option>
              @foreach($users as $user)
              <option value="{{ $user->id }}" {{ old('recipient_id', $recipientId ?? '') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->email }})
              </option>
              @endforeach
            </select>
            @error('recipient_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
            <select class="form-select @error('priority') is-invalid @enderror" name="priority" required>
              <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
              <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
              <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
              <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
            </select>
            @error('priority')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}" required>
          @error('subject')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('Message') }} <span class="text-danger">*</span></label>
          <textarea class="form-control @error('body') is-invalid @enderror" name="body" rows="8" required>{{ old('body') }}</textarea>
          @error('body')
          <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <!-- Link to Related Entity (Optional) -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Link to (Optional)') }}</label>
            <select class="form-select" name="related_type" id="related_type">
              <option value="">{{ __('None') }}</option>
              <option value="audit_plan" {{ old('related_type', $relatedType ?? '') === 'audit_plan' ? 'selected' : '' }}>{{ __('Audit Plan') }}</option>
              <option value="car" {{ old('related_type', $relatedType ?? '') === 'car' ? 'selected' : '' }}>{{ __('CAR') }}</option>
              <option value="complaint" {{ old('related_type', $relatedType ?? '') === 'complaint' ? 'selected' : '' }}>{{ __('Complaint') }}</option>
              <option value="document" {{ old('related_type', $relatedType ?? '') === 'document' ? 'selected' : '' }}>{{ __('Document') }}</option>
              <option value="certificate" {{ old('related_type', $relatedType ?? '') === 'certificate' ? 'selected' : '' }}>{{ __('Certificate') }}</option>
              <option value="external_audit" {{ old('related_type', $relatedType ?? '') === 'external_audit' ? 'selected' : '' }}>{{ __('External Audit') }}</option>
              <option value="improvement_opportunity" {{ old('related_type', $relatedType ?? '') === 'improvement_opportunity' ? 'selected' : '' }}>{{ __('Improvement Opportunity') }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Related ID') }}</label>
            <input type="number" class="form-control" name="related_id" value="{{ old('related_id', $relatedId ?? '') }}" placeholder="{{ __('Enter ID of related item') }}">
          </div>
        </div>

        @if($relatedModel ?? false)
        <div class="alert alert-info mb-3">
          <i class="icon-base ti tabler-info-circle me-1"></i>
          {{ __('This message will be linked to:') }} <strong>{{ class_basename($relatedModel) }} #{{ $relatedModel->id }}</strong>
          @if(isset($relatedModel->title))
          - {{ $relatedModel->title }}
          @elseif(isset($relatedModel->car_number))
          - {{ $relatedModel->car_number }}
          @elseif(isset($relatedModel->complaint_number))
          - {{ $relatedModel->complaint_number }}
          @endif
        </div>
        @endif

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti tabler-send me-1"></i> {{ __('Send Message') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: '{{ __("Select Recipient") }}',
      allowClear: true
    });
  });
</script>
@endpush
