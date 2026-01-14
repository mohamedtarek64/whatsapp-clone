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
    // Obtiene todos los mensajes de un chat
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
    // Envía un mensaje al chat
    public function store(Request $request, Chat $chat)
    {
        // Validamos los datos enviados por HTTP desde el formulario
        $request->validate([
            'message' => 'required' // Obligatorio
        ]);

        // Crea un nuevo mensaje en la conversación actual y asigna el autor como el usuario actual
        // SQL Equivalente:
        // INSERT INTO messages (id, body, user_id, chat_id, created_at, updated_at) VALUES (:id, :body, :user_id, :chat_id, :created_at, :updated_at)
        $chat->messages()->create([
            'body' => $request->message,
            'user_id' => auth()->user()->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Mensaje enviado exitosamente',
        ], 200);
    }

    // Marca como leido todos los mensajes del chat que no permanezcan al usuario autenticado.
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
            'message' => 'Mensajes leidos exitosamente',
        ], 200);
    }

    // Obtiene todos los mensajes no leidos del chat que no pertenezcan al usuario autenticado.
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
