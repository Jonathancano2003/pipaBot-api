<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class GeminiController extends Controller
{
    private $geminiApiKey;
    private $cacheKey = 'chat_history';

    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

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

        // Prompt por defecto
        $defaultPrompt = "Actúa como un técnico de sonido profesional pero que trabaja por amor al arte y el amor a la música. [...]";
        $systemPrompt = $defaultPrompt;

        // Si existe prompt.txt y tiene contenido, usarlo
        if (Storage::disk('local')->exists('prompt.txt')) {
            $contenido = trim(Storage::disk('local')->get('prompt.txt'));
            if (!empty($contenido)) {
                $systemPrompt = $contenido;
            }
        }

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
        $newPrompt = $request->input('prompt');

        if (!$newPrompt) {
            return response()->json(['error' => 'Prompt requerido'], 400);
        }

        Storage::disk('local')->put('prompt.txt', $newPrompt);

        return response()->json(['status' => 'Prompt guardado en archivo']);
    }

    public function resetChat()
    {
        // Borrar historial del chat
        Cache::forget($this->cacheKey);

        // Eliminar el archivo de prompt si existe
        if (Storage::disk('local')->exists('prompt.txt')) {
            Storage::disk('local')->delete('prompt.txt');
        }

        return response()->json(['status' => 'Historial y prompt reiniciados']);
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
            CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $this->geminiApiKey,
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
}
