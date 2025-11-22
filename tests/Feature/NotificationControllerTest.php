<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a test notification
     */
    protected function createTestNotification(array $attributes = []): Notification
    {
        return Notification::create(array_merge([
            'user_id' => $this->user->id,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test',
            'icon' => 'bell',
            'color' => 'info',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
        ], $attributes));
    }

    /**
     * Test notifications index page loads
     */
    public function test_notifications_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('notifications.index');
    }

    /**
     * Test notifications index shows user notifications
     */
    public function test_notifications_index_shows_user_notifications(): void
    {
        $this->createTestNotification(['title' => 'Test Notification']);

        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Notification');
    }

    /**
     * Test get unread count returns correct count
     */
    public function test_get_unread_count_returns_correct_count(): void
    {
        $this->createTestNotification(['title' => 'Unread 1']);
        $this->createTestNotification(['title' => 'Unread 2']);
        $this->createTestNotification(['title' => 'Read', 'read_at' => now()]);

        $response = $this->actingAs($this->user)->getJson(route('notifications.unread-count'));

        $response->assertStatus(200);
        $response->assertJson(['count' => 2]);
    }

    /**
     * Test get latest notifications
     */
    public function test_get_latest_notifications(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->createTestNotification(['title' => "Notification $i"]);
        }

        $response = $this->actingAs($this->user)->getJson(route('notifications.latest'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
        ]);

        // Should only return 10 notifications
        $this->assertCount(10, $response->json('notifications'));
    }

    /**
     * Test mark single notification as read
     */
    public function test_mark_notification_as_read(): void
    {
        $notification = $this->createTestNotification();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.mark-as-read', $notification));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test cannot mark another user's notification as read
     */
    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $otherUser = User::factory()->create();

        $notification = $this->createTestNotification(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.mark-as-read', $notification));

        $response->assertStatus(403);
    }

    /**
     * Test mark all notifications as read
     */
    public function test_mark_all_notifications_as_read(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createTestNotification(['title' => "Notification $i"]);
        }

        $this->assertEquals(5, $this->user->notifications()->unread()->count());

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.mark-all-as-read'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->notifications()->unread()->count());
    }

    /**
     * Test delete notification
     */
    public function test_delete_notification(): void
    {
        $notification = $this->createTestNotification();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.destroy', $notification));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    /**
     * Test cannot delete another user's notification
     */
    public function test_cannot_delete_another_users_notification(): void
    {
        $otherUser = User::factory()->create();

        $notification = $this->createTestNotification(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.destroy', $notification));

        $response->assertStatus(403);
    }

    /**
     * Test delete all notifications
     */
    public function test_delete_all_notifications(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createTestNotification(['title' => "Notification $i"]);
        }

        $this->assertEquals(5, $this->user->notifications()->count());

        $response = $this->actingAs($this->user)
            ->deleteJson(route('notifications.destroy-all'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $this->user->notifications()->count());
    }

    /**
     * Test notification model scopes
     */
    public function test_notification_unread_scope(): void
    {
        $unread = $this->createTestNotification(['title' => 'Unread']);
        $read = $this->createTestNotification(['title' => 'Read', 'read_at' => now()]);

        $unreadNotifications = Notification::unread()->get();
        $readNotifications = Notification::read()->get();

        $this->assertCount(1, $unreadNotifications);
        $this->assertCount(1, $readNotifications);
        $this->assertEquals($unread->id, $unreadNotifications->first()->id);
        $this->assertEquals($read->id, $readNotifications->first()->id);
    }

    /**
     * Test notification isRead and isUnread methods
     */
    public function test_notification_read_status_methods(): void
    {
        $notification = $this->createTestNotification();

        $this->assertTrue($notification->isUnread());
        $this->assertFalse($notification->isRead());

        $notification->markAsRead();

        $this->assertFalse($notification->isUnread());
        $this->assertTrue($notification->isRead());
    }
}
