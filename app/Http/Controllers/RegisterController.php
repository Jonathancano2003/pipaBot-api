<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:users,nombre',
            'password' => 'required|string|min:6',
        ]);

        $user = new User();
        $user->nombre = $request->nombre;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['success' => true]);
    }
}
