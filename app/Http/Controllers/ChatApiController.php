<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // __invoke => Method that executes when the class is invoked
    public function __invoke()
    {
        // Gets all chats that have at least 1 message.
        // $chats = auth()->user()->chats()->has('messages')->get();
        $chats = auth()->user()->chats()->has('messages')->get();
        return response()->json($chats);
    }
}
