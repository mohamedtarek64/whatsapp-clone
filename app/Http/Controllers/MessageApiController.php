<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Services\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MessageApiController extends Controller
{
    use ApiResponse;

    public function __construct(private MessageService $messageService)
    {
    }

    /**
     * Display all messages from a chat
     */
    public function index(Chat $chat)
    {
        $this->authorize('view', $chat);

        $messages = $chat->messages()
            ->withRelations()
            ->visibleToUser()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->paginated(
            $messages,
            'Messages retrieved successfully'
        );
    }

    /**
     * Store a newly created message
     */
    public function store(StoreMessageRequest $request, Chat $chat)
    {
        $this->authorize('sendMessage', $chat);

        try {
            $message = $this->messageService->sendMessage(
                $chat,
                $request->validated()['message']
            );

            return $this->success(
                new MessageResource($message),
                'Message sent successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to send message',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Mark all messages in chat as read
     */
    public function markAsRead(Chat $chat)
    {
        $this->authorize('view', $chat);

        try {
            $chat->messages()
                ->unread()
                ->update(['is_read' => true]);

            return $this->success(
                null,
                'Messages marked as read successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to mark messages as read',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get unread messages from chat
     */
    public function unreadMessages(Chat $chat)
    {
        $this->authorize('view', $chat);

        $userId = auth()->id();
        $cacheKey = "chat:{$chat->id}:user:{$userId}:unread";

        $unreadMessages = Cache::remember($cacheKey, 3, function () use ($chat) {
            return $chat->messages()
                ->unread()
                ->withRelations()
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return $this->success(
            MessageResource::collection($unreadMessages),
            'Unread messages retrieved successfully'
        );
    }
}

