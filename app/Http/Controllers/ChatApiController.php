<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Traits\ApiResponse;

class ChatApiController extends Controller
{
    use ApiResponse;

    /**
     * Display all chats for the authenticated user
     */
    public function __invoke()
    {
        $chats = auth()->user()->chats()
            ->has('messages')
            ->withRelations()
            ->latest('last_message_at')
            ->paginate(20);

        return $this->paginated(
            $chats,
            'Chats retrieved successfully'
        );
    }
}
