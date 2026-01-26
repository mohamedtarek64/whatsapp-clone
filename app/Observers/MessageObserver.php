<?php

namespace App\Observers;

use App\Models\Message;
use Illuminate\Support\Facades\Log;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        Log::channel('audit')->info('Message created', [
            'message_id' => $message->id,
            'chat_id' => $message->chat_id,
            'user_id' => $message->user_id,
            'type' => $message->type,
            'timestamp' => $message->created_at,
        ]);
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        Log::channel('audit')->info('Message updated', [
            'message_id' => $message->id,
            'changes' => $message->getChanges(),
            'user_id' => auth()?->id(),
        ]);
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        Log::channel('audit')->info('Message deleted', [
            'message_id' => $message->id,
            'chat_id' => $message->chat_id,
            'user_id' => auth()?->id(),
        ]);
    }
}
