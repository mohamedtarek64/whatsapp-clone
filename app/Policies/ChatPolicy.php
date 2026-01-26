<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the chat.
     */
    public function view(User $user, Chat $chat): bool
    {
        return $chat->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create messages in chat.
     */
    public function sendMessage(User $user, Chat $chat): bool
    {
        return $this->view($user, $chat);
    }

    /**
     * Determine whether the user can update the chat (admin only for groups).
     */
    public function update(User $user, Chat $chat): bool
    {
        if (!$this->view($user, $chat)) {
            return false;
        }

        if (!$chat->is_group) {
            return false;
        }

        return $chat->users()
            ->where('user_id', $user->id)
            ->where('is_admin', true)
            ->exists();
    }

    /**
     * Determine whether the user can delete the chat (admin only for groups).
     */
    public function delete(User $user, Chat $chat): bool
    {
        return $this->update($user, $chat);
    }
}
