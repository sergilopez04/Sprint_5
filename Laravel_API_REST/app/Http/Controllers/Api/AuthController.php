<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        // Validación de los campos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Cambiado a 'users'
            'password' => 'required|string|min:8',
            'nickname' => [
                'nullable',
                'string',
                'unique:users,nickname', // Cambiado a 'users'
                'max:25'
            ],
        ]);

        // Si la validación falla, devolver errores
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation Error.', 'details' => $validator->errors()], 422);
        }

        // Asignar el valor por defecto "Anonymous" si el nickname está vacío
        $nickname = $request->input('nickname') ?? 'Anonymous';

        // Crear el jugador/usuario
        $player = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'nickname' => $nickname,
        ]);

        // Asignar rol al jugador
        $player->assignRole($request->input('role', 'player'));

        // Crear el token de acceso
        $token = $player->createToken('dicegame')->accessToken;

        // Devolver la respuesta con el token y el nombre
        return response()->json([
            'message' => 'Player registered successfully',
            'token' => $token,
            'player' => [
                'name' => $player->name,
                'nickname' => $player->nickname
            ]
        ], 201);
    }



    // Método para iniciar sesión y obtener un token
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->accessToken;
    
            return response()->json(['token' => $token], 200);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    // Método para cerrar sesión
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
