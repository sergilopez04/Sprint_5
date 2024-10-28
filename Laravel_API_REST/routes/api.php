<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RollController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlayerController;  
use App\Http\Controllers\Api\AdminController;
use App\Models\Rolls;

// Rutas para autenticación de usuarios
Route::post('/player', [AuthController::class, 'register']);  // Registro de usuarios
Route::post('/login', [AuthController::class, 'login']);        // Iniciar sesión
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');  // Cerrar sesión

Route::group(["middleware" => ["auth:api"]], function() {
    Route::put('/players/{id}', [PlayerController::class, 'updateUserName']);
    Route::post('/players/{id}/games', [RollController::class, 'playGame']);
    Route::delete('/players/{id}/games', [RollController::class, 'deleteGames']);
    Route::get('/players/{id}/games', [RollController::class, 'showGames']);
});

Route::group(["middleware" => ["auth:api", "role:admin"]], function() {
    Route::get('/players', [AdminController::class, 'showAllPlayers']);
    Route::get('/players/ranking', [AdminController::class, 'getAverageRanking']);
    Route::get('/players/ranking/loser', [AdminController::class, 'getLoser']);
    Route::get('/players/ranking/winner', [AdminController::class, 'getWinner']);

});