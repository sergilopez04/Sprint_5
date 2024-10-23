<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rolls;
use Illuminate\Http\Request;

class RollController extends Controller
{
    const WINNING_SUM = 7;

    public function showGames(Request $request, string $id)
    {
        $user = $this->getUserOrFail($id);
        $this->authorizeUser($request->user(), $user);

        $games = $user->games;

        if ($games->isEmpty()) {
            return response()->json(['message' => 'No games recorded'], 200);
        }

        return response()->json($this->formatGamesResponse($games), 200);
    }

    public function playGame(Request $request, string $id)
    {
        $authenticatedUser = $request->user();
        $targetUser = User::find($id);

        if (is_null($targetUser)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->authorizeUser($authenticatedUser, $targetUser);

        $die1 = $this->rollDie();
        $die2 = $this->rollDie();
        $sum = $die1 + $die2;
        $result = ($sum === self::WINNING_SUM) ? 'Won' : 'Lost';
        $rollDate = now();

        Rolls::create([
            'player_id' => $authenticatedUser->id,
            'die1_value' => $die1,
            'die2_value' => $die2,
            'result' => $result,
            'roll_date' => $rollDate,
        ]);

        return response()->json($this->formatPlayGameResponse($result, $die1, $die2), 200);
    }

    private function rollDie(): int
    {
        return random_int(1, 6);
    }

    public function deleteGames(Request $request, string $id)
    {
        $user = $this->getUserOrFail($id);
        $this->authorizeUser($request->user(), $user);

        $games = $user->games;

        if ($games->isEmpty()) {
            return response()->json(['message' => 'No games to delete'], 200);
        }

        $deletedCount = $games->count(); // Fix: Correct deletion count
        $games->each->delete(); // Corrected the deletion method
        return response()->json(['message' => "{$deletedCount} games deleted."], 200);
    }

    private function getUserOrFail(string $id): User
    {
        $user = User::find($id);
        if (is_null($user)) {
            abort(404, 'User not found');
        }
        return $user;
    }

    private function authorizeUser(User $authenticatedUser, User $targetUser): void
    {
        if ($authenticatedUser->id !== $targetUser->id) {
            abort(403, 'You cannot access this user\'s games');
        }
    }

    private function formatGamesResponse($games): array
    {
        $totalGames = $games->count();
        $wonGames = $games->filter(function ($game) {
            return ($game->die1_value + $game->die2_value) === self::WINNING_SUM;
        })->count();
        $winPercentage = $totalGames > 0 ? ($wonGames / $totalGames) * 100 : 0;

        $formattedGames = $games->map(function ($game, $index) {
            return [
                'Game number' => $index + 1,
                'Die 1' => $game->die1_value,
                'Die 2' => $game->die2_value,
                'Result' => $game->result,
            ];
        });

        return [
            'Win percentage' => $winPercentage . "%",
            'Games played' => $formattedGames
        ];
    }

    private function formatPlayGameResponse(string $result, int $die1, int $die2): array
    {
        return [
            'message' => $result === 'Won' ? 'You win! Your dice values:' : 'You lose. Your dice values:',
            'Die 1' => $die1,
            'Die 2' => $die2,
            'Total' => $die1 + $die2,
        ];
    }
}