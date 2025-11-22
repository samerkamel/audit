@extends('layouts/layoutMaster')

@section('title', __('Create Reminder Setting'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Create Reminder Setting') }}</h4>
            <p class="text-muted mb-0">{{ __('Configure a new automatic reminder notification') }}</p>
        </div>
        <a href="{{ route('reminder-settings.index') }}" class="btn btn-outline-secondary">
            <i class="ti tabler-arrow-left me-1"></i> {{ __('Back to Settings') }}
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('reminder-settings.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-settings me-2"></i>
                    {{ __('Basic Settings') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">{{ __('Reminder Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="{{ __('e.g., Audit Start Reminder') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="entity_type" class="form-label">{{ __('Entity Type') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('entity_type') is-invalid @enderror"
                                id="entity_type" name="entity_type" required>
                            <option value="">{{ __('Select entity type...') }}</option>
                            @foreach($entityTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('entity_type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('entity_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">{{ __('What type of item should trigger reminders?') }}</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="event_type" class="form-label">{{ __('Event Type') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('event_type') is-invalid @enderror"
                                id="event_type" name="event_type" required>
                            <option value="">{{ __('Select event type...') }}</option>
                            @foreach($eventTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('event_type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">{{ __('What event should trigger reminders?') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-clock me-2"></i>
                    {{ __('Reminder Intervals') }}
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">{{ __('Select when reminders should be sent before the event:') }}</p>

                <div class="row">
                    @foreach($intervalOptions as $hours => $label)
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="intervals[]" value="{{ $hours }}"
                                       id="interval_{{ $hours }}"
                                       {{ in_array($hours, old('intervals', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="interval_{{ $hours }}">
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('intervals')
                    <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                @enderror
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-send me-2"></i>
                    {{ __('Notification Channels') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="send_email" name="send_email" value="1"
                                   {{ old('send_email', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_email">
                                <i class="ti tabler-mail me-1"></i> {{ __('Send Email') }}
                            </label>
                        </div>
                        <small class="form-text text-muted">{{ __('Receive reminders via email') }}</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="send_database" name="send_database" value="1"
                                   {{ old('send_database', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_database">
                                <i class="ti tabler-bell me-1"></i> {{ __('In-App Notification') }}
                            </label>
                        </div>
                        <small class="form-text text-muted">{{ __('Show in notification center') }}</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="is_active" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="ti tabler-toggle-right me-1"></i> {{ __('Active') }}
                            </label>
                        </div>
                        <small class="form-text text-muted">{{ __('Enable this reminder') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('reminder-settings.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti tabler-check me-1"></i> {{ __('Create Reminder') }}
            </button>
        </div>
    </form>
</div>
@endsection
