<?php

namespace App\Observers;

use App\Models\Chat;
use Illuminate\Support\Facades\Log;

class ChatObserver
{
    /**
     * Handle the Chat "created" event.
     */
    public function created(Chat $chat): void
    {
        Log::channel('audit')->info('Chat created', [
            'chat_id' => $chat->id,
            'is_group' => $chat->is_group,
            'users_count' => $chat->users()->count(),
        ]);
    }

    /**
     * Handle the Chat "updated" event.
     */
    public function updated(Chat $chat): void
    {
        Log::channel('audit')->info('Chat updated', [
            'chat_id' => $chat->id,
            'changes' => $chat->getChanges(),
            'user_id' => auth()?->id(),
        ]);
    }

    /**
     * Handle the Chat "deleted" event.
     */
    public function deleted(Chat $chat): void
    {
        Log::channel('audit')->info('Chat deleted', [
            'chat_id' => $chat->id,
            'user_id' => auth()?->id(),
        ]);
    }
}
