@extends('layouts/layoutMaster')

@section('title', __('Notification Templates'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h4 class="fw-bold mb-1">{{ __('Notification Templates') }}</h4>
            <p class="text-muted mb-0">{{ __('Customize email and in-app notification messages') }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <!-- Category Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('notification-templates.index') }}"
                   class="btn {{ !$category ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ __('All Templates') }}
                </a>
                @foreach($categories as $catKey => $catLabel)
                    <a href="{{ route('notification-templates.index', ['category' => $catKey]) }}"
                       class="btn {{ $category === $catKey ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $catLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Templates by Category -->
    @forelse($templates as $categoryKey => $categoryTemplates)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="icon-base ti tabler-mail me-2"></i>
                    {{ $categories[$categoryKey] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $categoryKey)) }}
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Template') }}</th>
                            <th>{{ __('Email Subject') }}</th>
                            <th class="text-center">{{ __('Email') }}</th>
                            <th class="text-center">{{ __('In-App') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categoryTemplates as $template)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $template->color_class }} rounded-circle p-2 me-2">
                                            <i class="icon-base ti {{ $template->notification_icon }}"></i>
                                        </span>
                                        <div>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->description)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($template->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-muted">{{ \Illuminate\Support\Str::limit($template->email_subject, 40) }}</code>
                                </td>
                                <td class="text-center">
                                    @if($template->send_email)
                                        <span class="badge bg-label-success"><i class="ti tabler-check"></i></span>
                                    @else
                                        <span class="badge bg-label-secondary"><i class="ti tabler-x"></i></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->send_database)
                                        <span class="badge bg-label-success"><i class="ti tabler-check"></i></span>
                                    @else
                                        <span class="badge bg-label-secondary"><i class="ti tabler-x"></i></span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->is_active)
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('notification-templates.edit', $template) }}"
                                           class="btn btn-sm btn-icon btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="{{ __('Edit Template') }}">
                                            <i class="ti tabler-edit"></i>
                                        </a>
                                        <form action="{{ route('notification-templates.toggle-status', $template) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm btn-icon {{ $template->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $template->is_active ? __('Deactivate') : __('Activate') }}">
                                                <i class="ti {{ $template->is_active ? 'tabler-toggle-right' : 'tabler-toggle-left' }}"></i>
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
                <i class="ti tabler-mail-off ti-xl text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('No notification templates found') }}</h5>
                <p class="text-muted mb-0">{{ __('Run the database seeder to create default templates.') }}</p>
            </div>
        </div>
    @endforelse

    <!-- Placeholder Legend -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="icon-base ti tabler-code me-2"></i>
                {{ __('Available Placeholders') }}
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">{{ __('Use these placeholders in your templates. They will be replaced with actual values when notifications are sent.') }}</p>
            <div class="row">
                <div class="col-md-4">
                    <h6>{{ __('Common') }}</h6>
                    <ul class="list-unstyled">
                        <li><code>{user_name}</code> - {{ __("Recipient's name") }}</li>
                        <li><code>{app_name}</code> - {{ __('Application name') }}</li>
                        <li><code>{action_url}</code> - {{ __('Link to view item') }}</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>{{ __('CAR Related') }}</h6>
                    <ul class="list-unstyled">
                        <li><code>{car_number}</code> - {{ __('CAR number') }}</li>
                        <li><code>{car_subject}</code> - {{ __('CAR subject') }}</li>
                        <li><code>{car_priority}</code> - {{ __('Priority level') }}</li>
                        <li><code>{car_due_date}</code> - {{ __('Due date') }}</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>{{ __('Document Related') }}</h6>
                    <ul class="list-unstyled">
                        <li><code>{document_number}</code> - {{ __('Document number') }}</li>
                        <li><code>{document_title}</code> - {{ __('Document title') }}</li>
                        <li><code>{document_status}</code> - {{ __('Current status') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
