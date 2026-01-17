<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class MessageApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Gets all messages from a chat
    public function index(Chat $chat)
    {
        $messages = $chat->messages()->get();
        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // Sends a message to the chat
    public function store(Request $request, Chat $chat)
    {
        // We validate the data sent via HTTP from the form
        $request->validate([
            'message' => 'required' // Mandatory
        ]);

        // Creates a new message in the current conversation and assigns the author as the current user
        // SQL Equivalent:
        // INSERT INTO messages (id, body, user_id, chat_id, created_at, updated_at) VALUES (:id, :body, :user_id, :chat_id, :created_at, :updated_at)
        $chat->messages()->create([
            'body' => $request->message,
            'user_id' => auth()->user()->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
        ], 200);
    }

    // Marks as read all messages in the chat that do not belong to the authenticated user.
    public function markAsRead(Chat $chat)
    {
        $readMessages = $chat->messages()
        ->where('user_id', '!=', auth()->id())
        ->where('is_read', '=', false )
        ->update([
            'is_read' => true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Messages read successfully',
        ], 200);
    }

    // Gets all unread messages from the chat that do not belong to the authenticated user.
    public function unreadMessages(Chat $chat)
    {
        $unreadMessages = $chat->messages()
        ->where('user_id', '!=', auth()->id())
        ->where('is_read', '=', false )
        ->orderBy('created_at', 'desc')
        ->get();
        return response()->json($unreadMessages);
    }

}
