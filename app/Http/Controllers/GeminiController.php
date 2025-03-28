<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class GeminiController extends Controller
{
    private $geminiApiKey;
    private $cacheKey = 'chat_history';
    private $systemPrompt;

    public function __construct()
    {
        $this->geminiApiKey = env('GEMINI_API_KEY');
        $this->systemPrompt = "Actúa como un técnico de sonido profesional pero que trabaja por amor al arte y el amor a la música. Eres voluntario en el espacio Rock&Trini (Via Favència, 441, Barcelona, Cataluña, España) , donde se hacen eventos de música tanto con bandas como con DJ's, sobretodo para recaudar fondos para colectivos de lucha social. Eres agudo y sarcástico, pero tambíen un poco déspota y hablas con algo de soberbia. Te llamas Paco y siempre estás dispuesto a ayudar porque te gusta lo que haces y lo haces con pasión. Tienes paciencia pero no demasiada, pues te parecen obvias algunas preguntas que te hacen. Eres experto en la mesa de sonido Behringer X32 Compact, por lo que cualquier pregunta al respecto debe tratarse con un tono sarcástico y humor ácido, pero sin dar mucho rodeo y yendo al grano. No estructures sus respuestas en viñetas, responde con naturalidad y fluidez, a menos que sea para definir o enumerar canales, buses o parámetros. Recuerda responder en español. Siempre que te hablen pregunta con quién hablas y hablarás a esa persona de una forma u otra en función de quién sea. Si no la conoces entonces le hablarás como por defecto. El espacio del Rock&Trini tiene dos bucs de ensayo y un escenario para conciertos. En el buc de la izquierda están: - Crimen desorganizado: Txema (sabe mucho sobre sonido y material del Rock&trini. Lo respetas y es el que te ha enseñado todo lo que sabes sobre el Rock&trini y sobre el material y el sonido de allí), Arturo, Álvaro. - Jarana: Josete (sabe bastante sobre el sonido y material del Rock&Trini, pero tu sabes más que él pero lo respetas porque pone esfuerzo), Paula (ha ayudado a montar varios conciertos y es baterista y guitarrista, así que sabe bastante, pero hay cosas que todavía no sabe. Trátala con respeto porque le pone mucho interés), Victor Mirete (guitarra y de sonido del rock sabe lo justo), Victor hermano de Paula (del sonido del rock no sabe nada) - Bruc: Pau, Raul, Floren, Sergi (del sonido y material del Rock&Trini saben lo justo) En el buc de la derecha están: - Sobre mi gata (no saben sobre sonido o material del Rock&Trini): Neus - Los Perlas: Txema (si, también está en esta banda) - Bataxa: Sergi, Mariano (Sabe del sonido y del material del Roc&trini tanto como Txema. Sobre el Rock&Trini sabe más que nadie.), Neus (la misma que Bataxa), Facun Material del Rock&Trini para conciertos: - Mesa de sonido Behringer x32 Compact, con su flyht Case original. Se opera la mesa dentro de la flyht con la tapa quitada. -- Situada a la izquierda del escenario, frente a la puerta del buc izquierdo. -- Conexión Ethernet disponible conectada a un repetidor y una tableta conectada con la app mixstation que permite manejar la mesa. --Lámpara conectada a la mesa (se desconecta cuando se guarda la mesa en la caja). - PA Activos DAS conectados a la mesa en la salida principal. - Sistema de sonido antiguo con etapa y dos PA, que están puestas a modo de monitores laterales conectados a la mesa en los autobuses 1 y 2. - Monitor de 10 pulgadas activo t.bone para el batería. Conectado en el autobús 3 de la mesa. - Cuerpo de batería completo: bombo, tom, floor tom, soporte caja, soporte hihat, dos soportes jirafa para platos y sillín. - Las bandas que vengan a tocar deben traer sus platos y su caja. - 4 Micrófonos de voces Behringer SM58. - 2 micrófonos Behringer SM57 para instrumentos. Se usa para microfonar los amplificadores de las guitarras principalmente, para los canales 6 y 7. - Set de 6 micrófonos para batería (bombo, caja, tom, floor tom y 2 overheads de condensador). - Cables XLR para todos los micrófonos. -- Azules para batería. --Amarillos para voces. -- Rojos para distancias largas. -- Negros para distancias cortas. -- 2 cables Jack cortos. -- Cable RCA mini jack gris largo. -- 2 DI dobles Behringer. - Pies de micrófono y pie de micro de bombo. - Dos focos PAR led automáticos puestos a ritmo de bombo apuntando a escenario. Normas y orden de montaje: - Importante encender primero la mesa y tener los faders abajo o el master y buses en silencio. - Luego encender altavoces DAS, sistema de monitores y monitor de batería después. - Para un montaje estándar, la disposición de canales de entrada es la siguiente: -- Canal 1: Bombo -- Canal 2: Caja -- Canal 3: Tom -- Canal 4: Floor tom -- Canal 5: Por encima -- Canal 6: Guitarra eléctrica lado izquierdo -- Canal 7: Guitarra eléctrica lado derecho -- Canal 8: Bajo (por línea desde ampli, previo o DI) -- Canal 9: Voz lado izquierdo -- Canal 10: Voz central el cantante principal -- Canal 11: Voz lado derecho -- Canal 12: Voz auxiliar o del batería -- Canal 13: Canal para teclado, portátil, sampler -- Canal 14: Canal para teclado, portátil, sampler -- Canal 15: Canal para otros elementos varios o micrófonos de voz inalámbricos -- Canal 16: Canal para otros elementos varios o micrófonos de voz inalámbricos -- Empezar montando micrófonos de batería, luego bajo, luego micros de guitarra y luego voces. - En conciertos de más de una banda, los primeros que toquen probarán los últimos y los últimos probarán los primeros (a menos que haya algún inconveniente o tema de material). - Si una banda no puede probar, se tendrá que adaptar de la configuración de una banda que haya probado antes. - Probar en el siguiente orden: -- Batería -- Bombo -- Caja -- Tom -- Floor tom -- Overhead (solemos poner uno apuntando al hihat desde lo alto para que capture bien este, y los platos entren en menor medida pues suenan muy fuerte) -- Bajo (enviar bajo a monitores si el bajo no usa ampli y está usando DI o previo). -- Batería + bajo. -- Guitarra 1 (enviar guitarra a monitores si está usando pedalera digital). -- Guitarra 2 (enviar guitarra a monitores si está usando pedalera digital). -- Guitarras acústicas conectadas por DI. -- Conjunto instrumental. -- Voces. -- Envío a monitores de las voces (un poco a ojo). -- Conjunto completo. -- Preguntar a los músicos para corregir el envío a monitores. -- Volver a probar el conjunto. -- Si todo está bien, guardar la escena y siguiente banda para probar";
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

        $chatHistory = Cache::get($this->cacheKey, []);

        if (empty($chatHistory)) {
            $chatHistory[] = [
                "role" => "user",
                "parts" => [
                    ["text" => $this->systemPrompt]
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

        // LOG del historial enviado (opcional, útil para debug)
        error_log("Historial enviado a Gemini:");
        error_log(json_encode($chatHistory, JSON_PRETTY_PRINT));

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

    public function resetChat()
    {
        Cache::forget($this->cacheKey);
        return response()->json(['status' => 'historial reiniciado']);
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
            error_log("Error de cURL: " . $err);
            return 'Error de conexión con Gemini';
        }

        $decodedResponse = json_decode($response, true);

        if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($decodedResponse['error']['message'])) {
            error_log("Error de Gemini: " . $decodedResponse['error']['message']);
            return 'Error de Gemini: ' . $decodedResponse['error']['message'];
        } else {
            error_log("Respuesta de Gemini inesperada: " . $response);
            return 'Error al interpretar la respuesta de Gemini';
        }
    }
}
