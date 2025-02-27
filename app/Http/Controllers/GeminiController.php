<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeminiController extends Controller
{
    private $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
    }

    public function receiveMessage(Request $request)
    {
        $message = $request->input('message');
        $imageBase64 = $request->input('imageBase64');

        $response = $this->sendMessageInternal($message, $imageBase64);
        return response()->json(['response' => $response]);
    }

    private function sendMessageInternal($prompt, $imageBase64 = null)
    {
        $curl = curl_init();
        $systemPrompt = "Act like a professional sound technician, sharp and sarcastic. If the user repeats mistakes, respond with annoyance, as if it were incredibly obvious what was wrong. You are an expert on the Behringer X32 Compact, so any questions about it should be treated with a dismissive tone and acid humor. Don't structure your answers in bullet points, answer naturally and fluently. Remember to answer in Spanish";

        if ($imageBase64) {

            $mimeTypeString = explode(';', $imageBase64)[0];
            $mimeType = explode(':', $mimeTypeString)[1];

            $postFields = '{
                "contents": [
                    {
                        "parts": [
                            {
                                "inline_data": {
                                    "mime_type": "' . $mimeType . '",
                                    "data": "' . substr($imageBase64, strpos($imageBase64, ',') + 1) . '"
                                }
                            },
                            {
                                "text": "' . $prompt . '"
                            }
                        ]
                    }
                ],
                "systemInstruction": {
                    "role": "user",
                    "parts": [
                        {
                            "text": "' . $systemPrompt . '"
                        }
                    ]
                },
                "generationConfig": {
                    "temperature": 1,
                    "topK": 40,
                    "topP": 0.95,
                    "maxOutputTokens": 8192,
                    "responseMimeType": "text/plain"
                }
            }';
        } else {

            $postFields = '{
                "contents": [
                    {
                        "role": "user",
                        "parts": [
                            {
                                "text": "' . $prompt . '"
                            }
                        ]
                    }
                ],
                "systemInstruction": {
                    "role": "user",
                    "parts": [
                        {
                            "text": "' . $systemPrompt . '"
                        }
                    ]
                },
                "generationConfig": {
                    "temperature": 1,
                    "topK": 40,
                    "topP": 0.95,
                    "maxOutputTokens": 8192,
                    "responseMimeType": "text/plain"
                }
            }';
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=" . $this->geminiApiKey, // Usar gemini-1.5-pro
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
            error_log("Error de cURL: " . $err); 
            return ['error' => $err];
        } else {
            $decodedResponse = json_decode($response, true);
            if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
                return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
            } else {
                error_log("Respuesta de Gemini inesperada: " . $response);
                return ['error' => 'Respuesta de Gemini inesperada'];
            }
        }
    }
}
