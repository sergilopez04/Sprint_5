<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rolls;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    // Crear un nuevo jugador (en este caso, ya es un usuario registrado)
    public function store(Request $request)
    {
        // Dado que el jugador es el mismo usuario, no es necesario crear uno nuevo
        return response()->json(['message' => 'El jugador ya está registrado como usuario.'], 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique',
            'nickname' => 'sometimes|string|max:255|unique:users,nickname,' . $id,
        ]);

        $player = User::findOrFail($id);
        $player->update($validatedData);

        return response()->json(['message' => 'El jugador ha sido actualizado con éxito.']);
    }

    public function games($id)
    {
        $player = User::findOrFail($id);
        $games = Rolls::where('player_id', $id)->get();

        return response()->json($games);
    }

    public function ranking()
    {
        $players = User::withCount(['rolls as success_rate' => function ($query) {
            $query->select(Rolls::raw('avg(result = "ganado")'));
        }])->orderByDesc('success_rate')->get();

        return response()->json($players);
    }

    public function loser()
    {
        $loser = User::withCount(['rolls as success_rate' => function ($query) {
            $query->select(Rolls::raw('avg(result = "ganado")'));
        }])->orderBy('success_rate')->first();

        return response()->json($loser);
    }

    public function winner()
    {
        $winner = User::withCount(['rolls as success_rate' => function ($query) {
            $query->select(Rolls::raw('avg(result = "ganado")'));
        }])->orderByDesc('success_rate')->first();

        return response()->json($winner);
    }
}