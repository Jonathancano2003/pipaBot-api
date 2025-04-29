<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatHistory;

class ChatHistoryController extends Controller
{
    public function index()
    {
        // Devolver solo los últimos 30 chats para evitar sobrecarga
        return ChatHistory::orderByDesc('created_at')->limit(30)->get();
    }

    public function store(Request $request)
    {
        $chat = ChatHistory::create([
            'titulo'   => $request->input('titulo'),
            'resumen'  => $request->input('resumen'),
            'mensajes' => json_encode($request->input('mensajes'), JSON_UNESCAPED_UNICODE)
        ]);

        return response()->json(['status' => 'Chat guardado', 'chat' => $chat]);
    }

    public function show($id)
    {
        $chat = ChatHistory::findOrFail($id);

        return response()->json([
            'id'       => $chat->id,
            'titulo'   => $chat->titulo,
            'resumen'  => $chat->resumen,
            'mensajes' => json_decode($chat->mensajes, true)
        ]);
    }
}
