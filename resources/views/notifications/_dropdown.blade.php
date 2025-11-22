<!-- Notification Dropdown -->
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
    <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" id="notificationDropdown">
        <span class="position-relative">
            <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
            <span class="badge rounded-pill bg-danger badge-dot badge-notifications border" id="notificationBadge" style="display: none;"></span>
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 360px;">
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h6 class="mb-0 me-auto">Notifications</h6>
                <div class="d-flex align-items-center h6 mb-0">
                    <span class="badge bg-label-primary me-2" id="unreadCount">0 New</span>
                    <a href="javascript:void(0)" class="dropdown-notifications-all p-2 btn btn-icon" id="markAllRead" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">
                        <i class="icon-base ti tabler-mail-opened text-heading"></i>
                    </a>
                </div>
            </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container" style="max-height: 400px; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="notificationsList">
                <li class="list-group-item text-center py-4">
                    <i class="icon-base ti tabler-bell-off text-muted icon-lg mb-2"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </li>
            </ul>
        </li>
        <li class="border-top">
            <div class="d-grid p-4">
                <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifications.index') }}">
                    <small class="align-middle">View all notifications</small>
                </a>
            </div>
        </li>
    </ul>
</li>

@push('page-scripts')
<script>
$(document).ready(function() {
    let refreshInterval;

    // Load notifications on page load
    loadNotifications();

    // Set up auto-refresh every 30 seconds
    refreshInterval = setInterval(loadNotifications, 30000);

    // Load notifications function
    function loadNotifications() {
        $.ajax({
            url: '{{ route('notifications.latest') }}',
            method: 'GET',
            success: function(response) {
                updateNotificationUI(response.notifications, response.unread_count);
            },
            error: function() {
                console.error('Failed to load notifications');
            }
        });
    }

    // Update notification UI
    function updateNotificationUI(notifications, unreadCount) {
        // Update badge
        if (unreadCount > 0) {
            $('#notificationBadge').show();
            $('#unreadCount').text(unreadCount + ' New');
        } else {
            $('#notificationBadge').hide();
            $('#unreadCount').text('0 New');
        }

        // Update notifications list
        const $list = $('#notificationsList');
        $list.empty();

        if (notifications.length === 0) {
            $list.append(`
                <li class="list-group-item text-center py-4">
                    <i class="icon-base ti tabler-bell-off text-muted icon-lg mb-2"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </li>
            `);
            return;
        }

        notifications.forEach(function(notification) {
            const isUnread = !notification.read_at;
            const readClass = isUnread ? '' : 'marked-as-read';
            const iconColor = notification.color || 'primary';
            const icon = notification.icon || 'bell';
            const timeAgo = moment(notification.created_at).fromNow();

            $list.append(`
                <li class="list-group-item list-group-item-action dropdown-notifications-item ${readClass}" data-id="${notification.id}">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                                <span class="avatar-initial rounded-circle bg-label-${iconColor}">
                                    <i class="icon-base ti tabler-${icon}"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <a href="${notification.action_url || 'javascript:void(0)'}" class="notification-link">
                                <h6 class="mb-1 small">${notification.title}</h6>
                                <small class="mb-1 d-block text-body">${notification.message}</small>
                                <small class="text-body-secondary">${timeAgo}</small>
                            </a>
                        </div>
                        <div class="flex-shrink-0 dropdown-notifications-actions">
                            ${isUnread ? `<a href="javascript:void(0)" class="dropdown-notifications-read mark-as-read" data-id="${notification.id}" title="Mark as read"><span class="badge badge-dot"></span></a>` : ''}
                            <a href="javascript:void(0)" class="dropdown-notifications-archive delete-notification" data-id="${notification.id}" title="Delete"><span class="icon-base ti tabler-x"></span></a>
                        </div>
                    </div>
                </li>
            `);
        });
    }

    // Mark notification as read
    $(document).on('click', '.mark-as-read', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const notificationId = $(this).data('id');

        $.ajax({
            url: `/notifications/${notificationId}/mark-as-read`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    });

    // Mark all as read
    $('#markAllRead').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route('notifications.mark-all-as-read') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    });

    // Delete notification
    $(document).on('click', '.delete-notification', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const notificationId = $(this).data('id');

        $.ajax({
            url: `/notifications/${notificationId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    });

    // Click on notification to navigate
    $(document).on('click', '.notification-link', function(e) {
        const $item = $(this).closest('.dropdown-notifications-item');
        const notificationId = $item.data('id');

        // Mark as read if unread
        if (!$item.hasClass('marked-as-read')) {
            $.ajax({
                url: `/notifications/${notificationId}/mark-as-read`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    });
});
</script>
@endpush
