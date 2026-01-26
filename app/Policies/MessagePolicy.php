<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $message->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the message for everyone.
     */
    public function deleteForEveryone(User $user, Message $message): bool
    {
        return $message->user_id === $user->id;
    }

    /**
     * Determine whether the user can add reactions to the message.
     */
    public function react(User $user, Message $message): bool
    {
        return $message->chat->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can star/unstar the message.
     */
    public function star(User $user, Message $message): bool
    {
        return $message->chat->users()->where('user_id', $user->id)->exists();
    }
}
