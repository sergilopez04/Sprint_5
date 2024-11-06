<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rolls;
use Illuminate\Http\Request;

class RollController extends Controller
{
    const WINNING_SUM = 7;
    const MESSAGE_NO_GAMES_RECORDED = 'No games recorded';
    const MESSAGE_USER_NOT_FOUND = 'User not found';
    const MESSAGE_NO_GAMES_TO_DELETE = 'No games to delete';
    const MESSAGE_GAMES_DELETED = '%d games deleted.';
    const MESSAGE_ACCESS_DENIED = 'You cannot access this user\'s games';

    public function showGames(Request $request, string $id)
    {
        $user = $this->authorizeAndGetUser($request, $id);
        $games = $user->games;

        if ($games->isEmpty()) {
            return $this->jsonResponse(self::MESSAGE_NO_GAMES_RECORDED, 200);
        }

        return response()->json($this->formatGamesResponse($games), 200);
    }

    public function playGame(Request $request, string $id)
    {
        $authenticatedUser = $request->user();
        $targetUser = User::findOrFail($id); 

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

    public function deleteGames(Request $request, string $id)
    {
        $user = $this->authorizeAndGetUser($request, $id);
        $games = $user->games;

        if ($games->isEmpty()) {
            return $this->jsonResponse(self::MESSAGE_NO_GAMES_TO_DELETE, 200);
        }

        $deletedCount = $games->count();
        $games->each->delete();
        return $this->jsonResponse(sprintf(self::MESSAGE_GAMES_DELETED, $deletedCount), 200);
    }

    private function authorizeAndGetUser(Request $request, string $id): User
    {
        $user = User::findOrFail($id);
        $this->authorizeUser($request->user(), $user);
        return $user;
    }

    private function authorizeUser(User $authenticatedUser, User $targetUser): void
    {
        if ($authenticatedUser->id !== $targetUser->id) {
            abort(403, self::MESSAGE_ACCESS_DENIED);
        }
    }

    private function formatGamesResponse($games): array
    {
        $totalGames = $games->count();
        $wonGames = $games->where('result', 'Won')->count();
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

    private function rollDie(): int
    {
        return random_int(1, 6);
    }

    private function jsonResponse(string $message, int $statusCode): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message], $statusCode);
    }
}

