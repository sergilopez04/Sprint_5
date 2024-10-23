<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rolls;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    const WIN_GAME = 7;
    public function showAllPlayers(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $users = User::all();

            if ($users->isEmpty()) {
                return response()->json([
                    'message' => 'No players found'
                ], 200);
            }

            return response()->json($users, 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

    
    public function getAverageRanking(Request $request){
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();
            if($players->isEmpty()){
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }
                $averageRanking = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WIN_GAME;
                })->count();
                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return $winPercentage;
            });

            $averageOfAverage = $averageRanking->avg();

            return response()->json([
                "status" => true,
                "average_ranking" => $averageOfAverage,
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

    public function getLoser(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();

            if ($players->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }
            if ($players->count() === 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'No hay otros jugadores para comparar.',
                    'loser' => $players->first(),
                ], 200);
            }

            $loser = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WIN_GAME;
                })->count();

                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return [
                    'player' => $player,
                    'winPercentage' => $winPercentage
                ];
            })->sortBy('winPercentage')->first();

            return response()->json([
                "status" => true,
                "loser" => $loser,
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Access to this information is not allowed"
            ], 403);
        }
    }

    public function getWinner(Request $request) {
        if ($request->user()->hasRole('admin')) {
            $players = User::has('games')->get();

            if ($players->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No players found'
                ], 404);
            }

            if ($players->count() === 1) {
                return response()->json([
                    'status' => true,
                    'message' => 'No hay otros jugadores para comparar.',
                    'winner' => $players->first(),
                ], 200);
            }


            $winner = $players->map(function ($player) {
                $totalGames = $player->games->count();
                $wonGames = $player->games->filter(function ($game) {
                    return ($game->dado1 + $game->dado2) === self::WIN_GAME;
                })->count();
                $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

                return [
                    'player' => $player,
                    'winPercentage' => $winPercentage,
                ];
            })->sortByDesc('winPercentage')->first();

            return response()->json([
                'status' => true,
                'winner' => $winner,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Access to this information is not allowed'
            ], 403);
        }
    }

}