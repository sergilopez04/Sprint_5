<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rolls;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Ver todos los jugadores y sus porcentajes de éxito
    public function index()
    {
        $players = User::withCount(['rolls as success_rate' => function ($query) {
            $query->select(Rolls::raw('avg(result = "ganado")'));
        }])->get();

        return response()->json($players);
    }

    // Ver el porcentaje de éxito promedio de todos los jugadores
    public function ranking()
    {
        $averageSuccess = User::withCount(['rolls as success_rate' => function ($query) {
            $query->select(Rolls::raw('avg(result = "ganado")'));
        }])->avg('success_rate');

        return response()->json(['average_success_rate' => $averageSuccess]);
    }
}