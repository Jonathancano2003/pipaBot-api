<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $nombre = $request->input('nombre');
        $password = $request->input('password');

        $usuario = User::where('nombre', $nombre)->first();

        if (!$usuario) {
            return response()->json(['success' => false, 'error' => 'Usuario no encontrado']);
        }

        if ($usuario->password === $password) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Contraseña incorrecta']);
    }
}
