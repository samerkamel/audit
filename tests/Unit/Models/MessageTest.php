<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_has_sender_relationship(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'priority' => 'normal',
        ]);

        $this->assertInstanceOf(User::class, $message->sender);
        $this->assertEquals($sender->id, $message->sender->id);
    }

    public function test_message_has_recipient_relationship(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'priority' => 'normal',
        ]);

        $this->assertInstanceOf(User::class, $message->recipient);
        $this->assertEquals($recipient->id, $message->recipient->id);
    }

    public function test_message_can_have_replies(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $parentMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Original Subject',
            'body' => 'Original Body',
            'priority' => 'normal',
        ]);

        $reply = Message::create([
            'sender_id' => $recipient->id,
            'recipient_id' => $sender->id,
            'parent_id' => $parentMessage->id,
            'subject' => 'Re: Original Subject',
            'body' => 'Reply Body',
            'priority' => 'normal',
        ]);

        $this->assertTrue($parentMessage->replies->contains($reply));
        $this->assertEquals($parentMessage->id, $reply->parent->id);
    }

    public function test_message_unread_scope(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $unreadMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Unread',
            'body' => 'Unread Body',
            'priority' => 'normal',
        ]);

        $readMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Read',
            'body' => 'Read Body',
            'priority' => 'normal',
            'read_at' => now(),
        ]);

        $unreadMessages = Message::unread()->get();

        $this->assertTrue($unreadMessages->contains($unreadMessage));
        $this->assertFalse($unreadMessages->contains($readMessage));
    }

    public function test_message_for_user_scope(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $otherUser = User::factory()->create();

        $messageForRecipient = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'For Recipient',
            'body' => 'Body',
            'priority' => 'normal',
        ]);

        $messageForOther = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $otherUser->id,
            'subject' => 'For Other',
            'body' => 'Body',
            'priority' => 'normal',
        ]);

        $recipientMessages = Message::forUser($recipient->id)->get();

        $this->assertTrue($recipientMessages->contains($messageForRecipient));
        $this->assertFalse($recipientMessages->contains($messageForOther));
    }

    public function test_message_mark_as_read(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'priority' => 'normal',
        ]);

        $this->assertFalse($message->isRead());

        $message->markAsRead();
        $message->refresh();

        $this->assertTrue($message->isRead());
        $this->assertNotNull($message->read_at);
    }

    public function test_message_priority_color_attribute(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $urgentMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Urgent',
            'body' => 'Body',
            'priority' => 'urgent',
        ]);

        $normalMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Normal',
            'body' => 'Body',
            'priority' => 'normal',
        ]);

        $this->assertEquals('danger', $urgentMessage->priority_color);
        $this->assertEquals('primary', $normalMessage->priority_color);
    }

    public function test_message_get_thread(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $parentMessage = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Original',
            'body' => 'Body',
            'priority' => 'normal',
        ]);

        $reply1 = Message::create([
            'sender_id' => $recipient->id,
            'recipient_id' => $sender->id,
            'parent_id' => $parentMessage->id,
            'subject' => 'Re: Original',
            'body' => 'Reply 1',
            'priority' => 'normal',
        ]);

        $reply2 = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'parent_id' => $parentMessage->id,
            'subject' => 'Re: Original',
            'body' => 'Reply 2',
            'priority' => 'normal',
        ]);

        $thread = $parentMessage->getThread();

        $this->assertCount(3, $thread);
        $this->assertTrue($thread->contains($parentMessage));
        $this->assertTrue($thread->contains($reply1));
        $this->assertTrue($thread->contains($reply2));
    }
}
