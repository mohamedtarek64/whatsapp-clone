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
    // __invoke => Método que se ejecuta cuando se invoca la clase
    public function __invoke()
    {
        // Obtiene los todos los chats que tengan almenos 1 mensaje.
        // $chats = auth()->user()->chats()->has('messages')->get();
        $chats = auth()->user()->chats()->has('messages')->get();
        return response()->json($chats);
    }
}
