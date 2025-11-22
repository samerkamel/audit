<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\CustomerComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display inbox (messages received by current user).
     */
    public function index(Request $request)
    {
        $query = Message::forUser(Auth::id())
            ->rootMessages()
            ->with(['sender', 'replies'])
            ->latest();

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->unread();
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('sender', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $messages = $query->paginate(15);

        // Statistics
        $statistics = [
            'total' => Message::forUser(Auth::id())->rootMessages()->count(),
            'unread' => Message::forUser(Auth::id())->rootMessages()->unread()->count(),
            'sent' => Message::sentBy(Auth::id())->rootMessages()->count(),
        ];

        $users = User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        return view('messages.index', compact('messages', 'statistics', 'users'));
    }

    /**
     * Display sent messages.
     */
    public function sent(Request $request)
    {
        $query = Message::sentBy(Auth::id())
            ->rootMessages()
            ->with(['recipient', 'replies'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(15);

        return view('messages.sent', compact('messages'));
    }

    /**
     * Show form for creating a new message.
     */
    public function create(Request $request)
    {
        $users = User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();

        // Pre-populate related entity if provided
        $relatedType = null;
        $relatedId = null;
        $relatedModel = null;

        if ($request->filled('related_type') && $request->filled('related_id')) {
            $relatedType = $request->related_type;
            $relatedId = $request->related_id;

            // Load the related model for display
            $modelClass = $this->getModelClass($relatedType);
            if ($modelClass) {
                $relatedModel = $modelClass::find($relatedId);
            }
        }

        // Pre-fill recipient if provided
        $recipientId = $request->get('recipient_id');

        return view('messages.create', compact('users', 'relatedType', 'relatedId', 'relatedModel', 'recipientId'));
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'related_type' => 'nullable|string|in:audit_plan,car,complaint,document,certificate,external_audit,improvement_opportunity',
            'related_id' => 'nullable|integer',
        ]);

        // Convert related_type to model class
        if ($validated['related_type']) {
            $validated['related_type'] = $this->getModelClass($validated['related_type']);
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'priority' => $validated['priority'],
            'related_type' => $validated['related_type'] ?? null,
            'related_id' => $validated['related_id'] ?? null,
        ]);

        return redirect()
            ->route('messages.index')
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message)
    {
        // Verify user has access to this message
        if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
            abort(403, 'You do not have access to this message.');
        }

        // Mark as read if recipient is viewing
        if ($message->recipient_id === Auth::id()) {
            $message->markAsRead();
        }

        // Load relationships
        $message->load(['sender', 'recipient', 'parent', 'replies.sender', 'replies.recipient', 'related']);

        // Get the full thread if this is part of a conversation
        $thread = $message->getThread();

        return view('messages.show', compact('message', 'thread'));
    }

    /**
     * Reply to a message.
     */
    public function reply(Request $request, Message $message)
    {
        // Verify user has access to reply
        if ($message->sender_id !== Auth::id() && $message->recipient_id !== Auth::id()) {
            abort(403, 'You do not have access to this message.');
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        // Determine recipient (the other person in the conversation)
        $recipientId = $message->sender_id === Auth::id()
            ? $message->recipient_id
            : $message->sender_id;

        // Get the root message for the parent_id
        $rootMessage = $message->parent_id ? $message->parent : $message;

        $reply = Message::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $recipientId,
            'parent_id' => $rootMessage->id,
            'subject' => 'Re: ' . $rootMessage->subject,
            'body' => $validated['body'],
            'priority' => $rootMessage->priority,
            'related_type' => $rootMessage->related_type,
            'related_id' => $rootMessage->related_id,
        ]);

        return redirect()
            ->route('messages.show', $rootMessage)
            ->with('success', 'Reply sent successfully.');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message)
    {
        if ($message->recipient_id !== Auth::id()) {
            abort(403, 'You do not have access to this message.');
        }

        $message->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all messages as read.
     */
    public function markAllAsRead()
    {
        Message::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()
            ->route('messages.index')
            ->with('success', 'All messages marked as read.');
    }

    /**
     * Delete a message (soft delete for user).
     */
    public function destroy(Message $message)
    {
        $userId = Auth::id();

        if ($message->sender_id === $userId) {
            $message->update(['sender_deleted' => true]);
        }

        if ($message->recipient_id === $userId) {
            $message->update(['recipient_deleted' => true]);
        }

        return redirect()
            ->route('messages.index')
            ->with('success', 'Message deleted.');
    }

    /**
     * Get unread count for current user (API endpoint).
     */
    public function getUnreadCount()
    {
        $count = Message::forUser(Auth::id())->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Convert related type string to model class.
     */
    protected function getModelClass(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        return match ($type) {
            'audit_plan' => AuditPlan::class,
            'car' => Car::class,
            'complaint' => CustomerComplaint::class,
            'document' => \App\Models\Document::class,
            'certificate' => \App\Models\Certificate::class,
            'external_audit' => \App\Models\ExternalAudit::class,
            'improvement_opportunity' => \App\Models\ImprovementOpportunity::class,
            default => null,
        };
    }
}
