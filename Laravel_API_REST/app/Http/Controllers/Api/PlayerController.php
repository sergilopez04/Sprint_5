<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rolls;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    const WIN_GAME = 7;
    public function store(Request $request)
    {
        return response()->json(['message' => 'El jugador ya está registrado como usuario.'], 201);
    }

    public function updatePlayer(Request $request, string $id) {
        $userToUpdate = User::findOrFail($id);
        $authUser = $request->user();

        if ($authUser->id !== $userToUpdate->id) {
            return response()->json([
                "message" => "You cannot modify another user's name."
            ], 403);
        }

        $request->validate([
            'name' => 'nullable|string',
        ]);

        $newName = empty($request->name) ? 'Anonymous' : $request->name;

        if($newName !== 'anónimo') {
            $existingUser = User::where('name', $newName)->first();
            if ($existingUser && $existingUser->id !== $userToUpdate->id) {
                return response()->json([
                    'message' => 'The name is already in use. Please choose another one.'
                ], 400);
            }
            $request->validate([
                'name' => 'unique:users,name',
            ]);
        }

        $userToUpdate->name = $newName;
        $userToUpdate->save();

        return response()->json([
            'message' => 'New Name is ' . $newName . ', Change Completed'
        ], 200);
    }


    

    public function games($id)
    {
        $player = User::findOrFail($id);
        $games = Rolls::where('player_id', $id)->get();

        return response()->json($games);
    }

}