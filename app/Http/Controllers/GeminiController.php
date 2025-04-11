<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Prompt;

class GeminiController extends Controller
{
    private $cacheKey = 'chat_history';

    public function receiveMessage(Request $request)
    {
        $message = $request->input('message');
        $imageBase64 = $request->input('image_base64');
        $imageMime = $request->input('image_mime', 'image/jpeg');

        if (!$message && !$imageBase64) {
            return response()->json(['error' => 'Mensaje o imagen requeridos'], 400);
        }

        if ($imageBase64 && !base64_decode($imageBase64, true)) {
            return response()->json(['error' => 'Imagen en base64 inválida'], 400);
        }

        // Obtener el último prompt de la base de datos
        $lastPrompt = Prompt::latest()->first();
        $systemPrompt = $lastPrompt?->content;

        if (!$systemPrompt) {
            return response()->json(['error' => 'No se encontró un prompt disponible'], 400);
        }

        // Cargar historial del chat
        $chatHistory = Cache::get($this->cacheKey, []);

        if (empty($chatHistory)) {
            $chatHistory[] = [
                "role" => "user",
                "parts" => [
                    ["text" => $systemPrompt]
                ]
            ];
        }

        if ($message) {
            $chatHistory[] = [
                "role" => "user",
                "parts" => [
                    ["text" => $message]
                ]
            ];
        }

        if ($imageBase64) {
            $chatHistory[] = [
                "role" => "user",
                "parts" => [
                    [
                        "inline_data" => [
                            "mime_type" => $imageMime,
                            "data" => $imageBase64
                        ]
                    ]
                ]
            ];
        }

        $response = $this->sendMessageInternal($chatHistory);

        if (is_string($response)) {
            $chatHistory[] = [
                "role" => "model",
                "parts" => [
                    ["text" => $response]
                ]
            ];
            Cache::put($this->cacheKey, $chatHistory, now()->addMinutes(60));
        }

        return Response::json(['response' => $response]);
    }

    public function updatePrompt(Request $request)
    {
        $newPrompt = trim($request->input('prompt'));
    
        if (!$newPrompt) {
            return response()->json(['error' => 'Prompt requerido'], 400);
        }
    
        // ✅ Verificar duplicado exacto
        if (Prompt::where('content', $newPrompt)->exists()) {
            return response()->json(['error' => 'Este prompt ya existe.'], 409); // 409 Conflict
        }
    
        Prompt::create([
            'content' => $newPrompt,
            'is_default' => false,
        ]);
    
        Storage::disk('local')->put('prompt.txt', $newPrompt);
    
        return response()->json(['status' => 'Prompt guardado en base de datos y archivo']);
    }
    




    public function getPrompt()
    {
        $latest = Prompt::latest()->first();

        if ($latest) {
            return response()->json(['prompt' => $latest->content]);
        }

        return response()->json(['prompt' => '']);
    }

    public function resetPrompt()
    {
        Cache::forget('chat_history');
        Prompt::truncate();

        return response()->json(['status' => 'Historial y prompts eliminados']);
    }

    public function getPromptHistory()
    {
        $prompts = Prompt::latest()->get(['id', 'content', 'created_at']); // 👈 AÑADE 'id'
        return response()->json($prompts);
    }
    

    public function resetChat()
    {
        Cache::forget($this->cacheKey);
        return response()->json(['status' => 'Historial de chat reiniciado']);
    }

    private function sendMessageInternal($chatHistory)
    {
        $postFields = json_encode([
            "contents" => $chatHistory,
            "generationConfig" => [
                "temperature" => 1,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 8192,
                "responseMimeType" => "text/plain"
            ]
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . env('GEMINI_API_KEY'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return 'Error de conexión con Gemini';
        }

        $decodedResponse = json_decode($response, true);

        if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($decodedResponse['error']['message'])) {
            return 'Error de Gemini: ' . $decodedResponse['error']['message'];
        } else {
            return 'Error al interpretar la respuesta de Gemini';
        }
    }
    public function deletePrompt($id)
    {
        $prompt = Prompt::find($id);

        if (!$prompt) {
            return response()->json(['error' => 'Prompt no encontrado'], 404);
        }

        $prompt->delete();

        return response()->json(['status' => 'Prompt eliminado']);
    }
}
