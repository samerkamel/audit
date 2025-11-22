@extends('layouts/layoutMaster')

@section('title', 'Notifications')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">Notifications</h1>
            <p class="text-muted mb-0">View and manage your notifications</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary" id="markAllRead">
                <i class="icon-base ti tabler-mail-opened me-1"></i>Mark All as Read
            </button>
            <button type="button" class="btn btn-outline-danger ms-2" id="deleteAll">
                <i class="icon-base ti tabler-trash me-1"></i>Delete All
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
            <div class="notification-item border-bottom p-4 {{ $notification->isRead() ? 'bg-light' : '' }}" data-id="{{ $notification->id }}">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <span class="avatar avatar-sm">
                            <span class="avatar-initial rounded-circle bg-label-{{ $notification->color }}">
                                <i class="icon-base ti tabler-{{ $notification->icon }}"></i>
                            </span>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                <small class="text-muted">
                                    <i class="icon-base ti tabler-clock"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                @if($notification->isUnread())
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill mark-as-read" data-id="{{ $notification->id }}" title="Mark as read">
                                    <i class="icon-base ti tabler-check"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill delete-notification" data-id="{{ $notification->id }}" title="Delete">
                                    <i class="icon-base ti tabler-x"></i>
                                </button>
                            </div>
                        </div>
                        @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-primary">
                            <i class="icon-base ti tabler-external-link me-1"></i>{{ $notification->action_text ?? 'View' }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="icon-base ti tabler-bell-off text-muted icon-lg mb-3"></i>
                <h5 class="text-muted">No notifications</h5>
                <p class="text-muted mb-0">You're all caught up!</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@push('page-scripts')
<script>
$(document).ready(function() {
    // Mark as read
    $('.mark-as-read').on('click', function() {
        const notificationId = $(this).data('id');
        const $item = $(this).closest('.notification-item');

        $.ajax({
            url: `/notifications/${notificationId}/mark-as-read`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                $item.addClass('bg-light');
                $item.find('.mark-as-read').remove();
            }
        });
    });

    // Mark all as read
    $('#markAllRead').on('click', function() {
        $.ajax({
            url: '{{ route('notifications.mark-all-as-read') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                $('.notification-item').addClass('bg-light');
                $('.mark-as-read').remove();
            }
        });
    });

    // Delete notification
    $('.delete-notification').on('click', function() {
        const notificationId = $(this).data('id');
        const $item = $(this).closest('.notification-item');

        if (confirm('Are you sure you want to delete this notification?')) {
            $.ajax({
                url: `/notifications/${notificationId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    $item.fadeOut(300, function() {
                        $(this).remove();

                        // Check if no notifications left
                        if ($('.notification-item').length === 0) {
                            location.reload();
                        }
                    });
                }
            });
        }
    });

    // Delete all
    $('#deleteAll').on('click', function() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            $.ajax({
                url: '{{ route('notifications.destroy-all') }}',
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    location.reload();
                }
            });
        }
    });
});
</script>
@endpush
@endsection
