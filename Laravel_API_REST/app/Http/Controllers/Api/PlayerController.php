<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    const WIN_GAME = 7;
    const MESSAGE_PLAYER_CREATED = 'The player has already been created.';
    const MESSAGE_UNAUTHORIZED = "You cannot modify another user's name.";
    const MESSAGE_NAME_CONFLICT = 'The name is already in use. Please choose another one.';
    const MESSAGE_NAME_UPDATED = 'New name is %s, change completed.';

    public function store()
    {
        return response()->json(['message' => self::MESSAGE_PLAYER_CREATED], 201);
    }

    public function updateUserName(Request $request, string $userId)
    {
        try {
            $userToUpdate = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $authenticatedUser = $request->user();

        if ($this->isUnauthorizedUser($authenticatedUser, $userToUpdate)) {
            return $this->unauthorizedResponse();
        }

        $request->validate(['name' => 'nullable|string']);

        $updatedName = $request->name ?: 'Anonymous';

        if ($this->isNameConflict($updatedName, $userToUpdate)) {
            return $this->nameConflictResponse();
        }

        $userToUpdate->update(['name' => $updatedName]);

        return $this->successResponse($updatedName);
    }

    private function isUnauthorizedUser(User $authenticatedUser, User $userToUpdate): bool
    {
        return $authenticatedUser->id !== $userToUpdate->id;
    }

    private function unauthorizedResponse()
    {
        return response()->json(['message' => self::MESSAGE_UNAUTHORIZED], 403);
    }

    private function isNameConflict(string $updatedName, User $userToUpdate): bool
    {
        if ($updatedName === 'Anonymous') {
            return false;
        }

        return User::where('name', $updatedName)->where('id', '!=', $userToUpdate->id)->exists();
    }

    private function nameConflictResponse()
    {
        return response()->json(['message' => self::MESSAGE_NAME_CONFLICT], 400);
    }

    private function successResponse(string $updatedName)
    {
        return response()->json(['message' => sprintf(self::MESSAGE_NAME_UPDATED, $updatedName)], 200);
    }
}
