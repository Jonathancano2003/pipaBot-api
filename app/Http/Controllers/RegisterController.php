<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
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

        // Validación
        $request->validate([
            'nombre' => 'required|string|max:255|unique:users,nombre',
            'password' => 'required|string|min:6',
        ]);

        // Registro
        $user = new User();
        $user->nombre = $request->nombre;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['success' => true]);
    }
}
