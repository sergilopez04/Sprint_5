<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rolls;
use Illuminate\Http\Request;

class RollController extends Controller
{
    // Crear un nuevo roll
    public function store(Request $request)
    {
        // Obtener el ID del usuario autenticado
        $playerId = $request->user()->id;

        $die1Value = $this->rollDie();
        $die2Value = $this->rollDie();

        $sum = $die1Value + $die2Value;
        $result = ($sum === 7) ? 'ganado' : 'perdido';
        $rollDate = now();

        $roll = Rolls::create([
            'player_id' => $playerId,
            'die1_value' => $die1Value,
            'die2_value' => $die2Value,
            'result' => $result,
            'roll_date' => $rollDate,
        ]);

        return response()->json($roll, 201);
    }

    private function rollDie()
    {
        return rand(1, 6); 
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

    // // Eliminar un roll
    // public function destroy($id)
    // {
    //     $roll = Rolls::findOrFail($id);
    //     $roll->delete();
    //     return response()->json(null, 204);
    // }
}
