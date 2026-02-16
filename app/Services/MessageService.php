<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use App\Notifications\NewMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class MessageService
{
    /**
     * Send a message in a chat.
     */
    public function sendMessage(Chat $chat, $body, $media = null, $parentId = null)
    {
        $type = 'text';
        $filePath = null;

        if ($media) {
            try {
                $extension = $media->getClientOriginalExtension();
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $type = 'image';
                } else {
                    $type = 'file';
                }
                $filePath = $media->store('media', 'public');
            } catch (\Exception $e) {
                report($e);
                $filePath = null;
                $type = 'text';
            }
        }

        $message = $chat->messages()->create([
            'body' => $body,
            'user_id' => Auth::id(),
            'type' => $type,
            'file_path' => $filePath,
            'parent_id' => $parentId,
        ]);

        $chat->update([
            'last_message_at' => $message->created_at
        ]);

        $this->broadcastNewMessage($chat, $message);

        return $message;
    }

    /**
     * Broadcast the new message notification.
     */
    protected function broadcastNewMessage(Chat $chat, Message $message)
    {
        $usersToNotify = $chat->users()->where('users.id', '!=', Auth::id())->get();
        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new NewMessage($chat, $message));
        }
    }

    /**
     * Delete a message for the current user only.
     */
    public function deleteForMe(Message $message)
    {
        return \App\Models\DeletedMessage::create([
            'user_id' => Auth::id(),
            'message_id' => $message->id,
        ]);
    }

    /**
     * Delete a message for everyone in the chat.
     */
    public function deleteForEveryone(Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            throw new \Exception("You can only delete your own messages for everyone.");
        }

        $message->update([
            'body' => 'This message was deleted',
            'type' => 'text',
            'file_path' => null,
            'deleted_for_everyone' => true,
        ]);

        return $message;
    }

    /**
     * Clear all messages in a chat for the current user.
     */
    public function clearChat(Chat $chat)
    {
        $userId = Auth::id();

        // Process messages in chunks and insert ignoring duplicates to avoid high memory usage
        $chat->messages()->select('id')->chunk(500, function ($messages) use ($userId) {
            $rows = [];
            $now = now();
            foreach ($messages as $m) {
                $rows[] = [
                    'user_id' => $userId,
                    'message_id' => $m->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                DB::table('deleted_messages')->insertOrIgnore($rows);
            }
        });
    }
}
