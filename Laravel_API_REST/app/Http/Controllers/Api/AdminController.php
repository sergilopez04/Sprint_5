<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    const WIN_GAME = 7;
    const MESSAGE_NO_PLAYERS_FOUND = 'No players found';
    const MESSAGE_ACCESS_DENIED = "Access to this information is not allowed";
    const MESSAGE_SINGLE_PLAYER_COMPARISON = 'There ain\'t any players to compare to.';

    public function showAllPlayers(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->accessDeniedResponse();
        }

        $users = User::all();

        return $users->isEmpty()
            ? response()->json(['message' => self::MESSAGE_NO_PLAYERS_FOUND], 200)
            : response()->json($users, 200);
    }

    public function getAverageRanking(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->accessDeniedResponse();
        }

        $players = User::has('games')->get();

        if ($players->isEmpty()) {
            return response()->json(['status' => false, 'message' => self::MESSAGE_NO_PLAYERS_FOUND], 404);
        }

        $averageRanking = $this->calculateWinPercentages($players);
        $averageOfAverage = $averageRanking->avg();

        return response()->json(['status' => true, 'average_ranking' => $averageOfAverage], 200);
    }

    public function getLoser(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->accessDeniedResponse();
        }

        return $this->getPlayerResponse('loser');
    }

    public function getWinner(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return $this->accessDeniedResponse();
        }

        return $this->getPlayerResponse('winner');
    }

    private function getPlayerResponse($type)
    {
        $players = User::has('games')->get();

        if ($players->isEmpty()) {
            return response()->json(['status' => false, 'message' => self::MESSAGE_NO_PLAYERS_FOUND], 404);
        }

        return $this->handleSinglePlayerResponse($players, $type);
    }

    private function handleSinglePlayerResponse($players, $type)
    {
        if ($players->count() === 1) {
            return response()->json([
                'status' => true,
                'message' => self::MESSAGE_SINGLE_PLAYER_COMPARISON,
                $type => $players->first(),
            ], 200);
        }

        $result = $this->calculatePlayerStatistics($players, $type === 'winner');

        return response()->json(['status' => true, $type => $result], 200);
    }

    private function calculateWinPercentages($players)
    {
        return $players->map(function ($player) {
            $totalGames = $player->games->count();
            $wonGames = $this->countWonGames($player);
            return $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;
        });
    }

    private function countWonGames($player)
    {
        return $player->games->filter(function ($game) {
            return ($game->die1 + $game->die2) === self::WIN_GAME;
        })->count();
    }

    private function calculatePlayerStatistics($players, $isWinner)
    {
        $sortedPlayers = $players->map(function ($player) {
            $totalGames = $player->games->count();
            $wonGames = $this->countWonGames($player);
            $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

            return [
                'player' => $player,
                'winPercentage' => $winPercentage,
            ];
        });

        return $isWinner ? $sortedPlayers->sortByDesc('winPercentage')->first() : $sortedPlayers->sortBy('winPercentage')->first();
    }

    private function accessDeniedResponse()
    {
        return response()->json([
            "status" => false,
            "message" => self::MESSAGE_ACCESS_DENIED
        ], 403);
    }
}
