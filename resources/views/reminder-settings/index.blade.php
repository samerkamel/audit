@extends('layouts/layoutMaster')

@section('title', __('Reminder Settings'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Reminder Settings') }}</h4>
            <p class="text-muted mb-0">{{ __('Configure automatic notification reminders for upcoming events') }}</p>
        </div>
        <a href="{{ route('reminder-settings.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> {{ __('Add Reminder') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <!-- Reminder Settings by Entity Type -->
    @forelse($settings as $entityType => $entitySettings)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    @switch($entityType)
                        @case('audit_plan')
                            <i class="ti tabler-clipboard-check me-2"></i> {{ __('Internal Audit Reminders') }}
                            @break
                        @case('external_audit')
                            <i class="ti tabler-file-certificate me-2"></i> {{ __('External Audit Reminders') }}
                            @break
                        @case('car')
                            <i class="ti tabler-alert-circle me-2"></i> {{ __('CAR Reminders') }}
                            @break
                        @case('certificate')
                            <i class="ti tabler-award me-2"></i> {{ __('Certificate Reminders') }}
                            @break
                        @case('document')
                            <i class="ti tabler-file-text me-2"></i> {{ __('Document Reminders') }}
                            @break
                        @default
                            <i class="ti tabler-bell me-2"></i> {{ $entityTypes[$entityType] ?? ucfirst(str_replace('_', ' ', $entityType)) }}
                    @endswitch
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Event Type') }}</th>
                            <th>{{ __('Reminder Intervals') }}</th>
                            <th class="text-center">{{ __('Email') }}</th>
                            <th class="text-center">{{ __('In-App') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entitySettings as $setting)
                            <tr>
                                <td>
                                    <strong>{{ $setting->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $setting->event_type_label }}</span>
                                </td>
                                <td>
                                    @foreach($setting->intervals as $interval)
                                        <span class="badge bg-label-primary me-1">
                                            {{ $intervalOptions[$interval] ?? $interval . 'h' }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    @if($setting->send_email)
                                        <span class="badge bg-label-success"><i class="ti tabler-check"></i></span>
                                    @else
                                        <span class="badge bg-label-secondary"><i class="ti tabler-x"></i></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($setting->send_database)
                                        <span class="badge bg-label-success"><i class="ti tabler-check"></i></span>
                                    @else
                                        <span class="badge bg-label-secondary"><i class="ti tabler-x"></i></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($setting->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('reminder-settings.edit', $setting) }}"
                                           class="btn btn-sm btn-icon btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="{{ __('Edit') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route('reminder-settings.toggle-status', $setting) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm btn-icon {{ $setting->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $setting->is_active ? __('Deactivate') : __('Activate') }}">
                                                <i class="ti {{ $setting->is_active ? 'tabler-toggle-right' : 'tabler-toggle-left' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('reminder-settings.destroy', $setting) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-icon btn-outline-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Delete') }}">
                                                <i class="ti tabler-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti tabler-bell-off ti-xl text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No reminder settings configured') }}</h5>
                <p class="text-muted mb-4">{{ __('Create reminder settings to receive notifications before important events.') }}</p>
                <a href="{{ route('reminder-settings.create') }}" class="btn btn-primary">
                    <i class="ti tabler-plus me-1"></i> {{ __('Create First Reminder') }}
                </a>
            </div>
        </div>
    @endforelse

    <!-- How It Works Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="ti tabler-info-circle me-2"></i>
                {{ __('How Reminders Work') }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>{{ __('Reminder Intervals') }}</h6>
                    <p class="text-muted">
                        {{ __('Select multiple intervals for each event type. For example, you can receive reminders at 72 hours (3 days), 24 hours (1 day), and 1 hour before an audit starts.') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6>{{ __('Notification Channels') }}</h6>
                    <p class="text-muted">
                        {{ __('Choose how you want to receive reminders:') }}
                    </p>
                    <ul class="text-muted mb-0">
                        <li><strong>{{ __('Email') }}</strong> - {{ __('Receive reminder emails') }}</li>
                        <li><strong>{{ __('In-App') }}</strong> - {{ __('See notifications in the application') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
