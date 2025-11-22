@extends('layouts/layoutMaster')

@section('title', 'Edit Notification Template')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('notification-templates.index') }}">Notification Templates</a>
                    </li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-1">Edit Template: {{ $notificationTemplate->name }}</h4>
            <p class="text-muted mb-0">{{ $notificationTemplate->description }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('notification-templates.index') }}" class="btn btn-outline-secondary">
                <i class="ti tabler-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('notification-templates.update', $notificationTemplate) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-info-circle me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Template Code</label>
                                <input type="text" class="form-control" value="{{ $notificationTemplate->code }}" disabled>
                                <small class="text-muted">System identifier (cannot be changed)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" value="{{ $notificationTemplate->category_label }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="name">Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $notificationTemplate->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2">{{ old('description', $notificationTemplate->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Template -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-mail me-2"></i>Email Template
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="email_subject">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('email_subject') is-invalid @enderror"
                                   id="email_subject" name="email_subject"
                                   value="{{ old('email_subject', $notificationTemplate->email_subject) }}" required>
                            @error('email_subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="email_body">Email Body <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('email_body') is-invalid @enderror"
                                      id="email_body" name="email_body" rows="10"
                                      style="font-family: monospace;">{{ old('email_body', $notificationTemplate->email_body) }}</textarea>
                            @error('email_body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supports plain text. Use placeholders like <code>{user_name}</code> for dynamic content.</small>
                        </div>
                    </div>
                </div>

                <!-- In-App Notification -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-bell me-2"></i>In-App Notification
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="notification_title">Notification Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('notification_title') is-invalid @enderror"
                                   id="notification_title" name="notification_title"
                                   value="{{ old('notification_title', $notificationTemplate->notification_title) }}" required>
                            @error('notification_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="notification_message">Notification Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('notification_message') is-invalid @enderror"
                                      id="notification_message" name="notification_message" rows="3">{{ old('notification_message', $notificationTemplate->notification_message) }}</textarea>
                            @error('notification_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Appearance -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-palette me-2"></i>Appearance
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="notification_icon">Icon</label>
                            <select class="form-select @error('notification_icon') is-invalid @enderror"
                                    id="notification_icon" name="notification_icon">
                                @foreach($icons as $iconClass => $iconName)
                                    <option value="{{ $iconClass }}"
                                            {{ old('notification_icon', $notificationTemplate->notification_icon) === $iconClass ? 'selected' : '' }}>
                                        {{ $iconName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('notification_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="notification_color">Color</label>
                            <select class="form-select @error('notification_color') is-invalid @enderror"
                                    id="notification_color" name="notification_color">
                                @foreach($colors as $colorKey => $colorName)
                                    <option value="{{ $colorKey }}"
                                            {{ old('notification_color', $notificationTemplate->notification_color) === $colorKey ? 'selected' : '' }}>
                                        {{ $colorName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('notification_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Delivery Channels -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-send me-2"></i>Delivery Channels
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="send_email" name="send_email" value="1"
                                   {{ old('send_email', $notificationTemplate->send_email) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_email">
                                <i class="ti tabler-mail me-1"></i>Send Email
                            </label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="send_database" name="send_database" value="1"
                                   {{ old('send_database', $notificationTemplate->send_database) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_database">
                                <i class="ti tabler-bell me-1"></i>In-App Notification
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $notificationTemplate->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="ti tabler-toggle-right me-1"></i>Template Active
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Available Placeholders -->
                @if($notificationTemplate->available_placeholders)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ti tabler-code me-2"></i>Placeholders
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">Available for this template:</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($notificationTemplate->available_placeholders as $placeholder)
                                <code class="badge bg-light text-dark">{!! '{' . $placeholder . '}' !!}</code>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="ti tabler-device-floppy me-1"></i>Save Changes
                        </button>
                        <a href="{{ route('notification-templates.index') }}" class="btn btn-outline-secondary w-100">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
