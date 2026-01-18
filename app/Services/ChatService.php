<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    /**
     * Create or retrieve a one-on-one chat.
     */
    public function getOrCreateDirectChat($userId, $contactUserId)
    {
        $chat = Auth::user()->chats()
            ->whereHas('users', function ($query) use ($contactUserId) {
                $query->where('users.id', $contactUserId);
            })
            ->where('is_group', false)
            ->first();

        if (!$chat) {
            $chat = Chat::create(['is_group' => false]);
            $chat->users()->attach([$userId, $contactUserId]);
        }

        return $chat;
    }

    /**
     * Create a group chat.
     */
    public function createGroupChat($name, array $contactIds, $imagePath = null)
    {
        $chat = Chat::create([
            'name' => $name,
            'is_group' => true,
            'image_url' => $imagePath,
            'last_message_at' => now(),
        ]);

        $chat->users()->attach(Auth::id());

        $userIds = Contact::whereIn('id', $contactIds)->pluck('contact_id');
        $chat->users()->attach($userIds);

        return $chat;
    }
}
