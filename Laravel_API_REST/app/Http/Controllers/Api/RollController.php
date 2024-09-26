<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rolls;
use Illuminate\Http\Request;

class RollController extends Controller
{
    // Obtener todos los rolls
    public function index()
    {
        return Rolls::all();
    }

    // Crear un nuevo roll
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'player_id' => 'required|exists:users,id',
            'die1_value' => 'required|integer',
            'die2_value' => 'required|integer',
            'result' => 'required|in:ganado,perdido',
            'roll_date' => 'required|date',
        ]);

        $roll = Rolls::create($validatedData);
        return response()->json($roll, 201);
    }

    // Obtener un roll específico
    public function show($id)
    {
        $roll = Rolls::findOrFail($id);
        return response()->json($roll);
    }

    // Actualizar un roll existente
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'player_id' => 'sometimes|exists:users,id',
            'die1_value' => 'sometimes|integer',
            'die2_value' => 'sometimes|integer',
            'result' => 'sometimes|in:ganado,perdido',
            'roll_date' => 'sometimes|date',
        ]);

        $roll = Rolls::findOrFail($id);
        $roll->update($validatedData);
        return response()->json($roll, 200);
    }

    // Eliminar un roll
    public function destroy($id)
    {
        $roll = Rolls::findOrFail($id);
        $roll->delete();
        return response()->json(null, 204);
    }
}
