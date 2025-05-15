<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Verificar reCAPTCHA
        $recaptcha = $request->input('recaptcha');

        $captchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $recaptcha
        ]);

        if (!$captchaResponse->json('success')) {
            return response()->json(['success' => false, 'error' => 'Captcha inválido']);
        }

        // Verificar usuario
        $nombre = $request->input('nombre');
        $password = $request->input('password');

        $usuario = User::where('nombre', $nombre)->first();

        if (!$usuario || !Hash::check($password, $usuario->password)) {
            return response()->json(['success' => false, 'error' => 'Usuario o contraseña incorrectos']);
        }

        return response()->json(['success' => true]);
    }
}
