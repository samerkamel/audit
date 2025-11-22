@extends('layouts/layoutMaster')

@section('title', 'Edit Reminder Setting')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="fw-bold mb-1">Edit Reminder Setting</h4>
            <p class="text-muted mb-0">{{ $reminderSetting->name }}</p>
        </div>
        <a href="{{ route('reminder-settings.index') }}" class="btn btn-outline-secondary">
            <i class="ti tabler-arrow-left me-1"></i> Back to Settings
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

    <form action="{{ route('reminder-settings.update', $reminderSetting) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-settings me-2"></i>
                    Basic Settings
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Reminder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $reminderSetting->name) }}"
                               placeholder="e.g., Audit Start Reminder" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Entity Type</label>
                        <input type="text" class="form-control" value="{{ $reminderSetting->entity_type_label }}" readonly disabled>
                        <small class="form-text text-muted">Entity type cannot be changed after creation</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Event Type</label>
                        <input type="text" class="form-control" value="{{ $reminderSetting->event_type_label }}" readonly disabled>
                        <small class="form-text text-muted">Event type cannot be changed after creation</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ti tabler-clock me-2"></i>
                    Reminder Intervals
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Select when reminders should be sent before the event:</p>

                <div class="row">
                    @foreach($intervalOptions as $hours => $label)
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="intervals[]" value="{{ $hours }}"
                                       id="interval_{{ $hours }}"
                                       {{ in_array($hours, old('intervals', $reminderSetting->intervals ?? [])) ? 'checked' : '' }}>
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
                    Notification Channels
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="send_email" name="send_email" value="1"
                                   {{ old('send_email', $reminderSetting->send_email) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_email">
                                <i class="ti tabler-mail me-1"></i> Send Email
                            </label>
                        </div>
                        <small class="form-text text-muted">Receive reminders via email</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="send_database" name="send_database" value="1"
                                   {{ old('send_database', $reminderSetting->send_database) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_database">
                                <i class="ti tabler-bell me-1"></i> In-App Notification
                            </label>
                        </div>
                        <small class="form-text text-muted">Show in notification center</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $reminderSetting->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="ti tabler-toggle-right me-1"></i> Active
                            </label>
                        </div>
                        <small class="form-text text-muted">Enable this reminder</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('reminder-settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti tabler-check me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
