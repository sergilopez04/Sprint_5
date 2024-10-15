<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RollController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlayerController;  
use App\Http\Controllers\Api\AdminController;   

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas para autenticación de usuarios
Route::post('/register', [AuthController::class, 'register']);  // Registro de usuarios
Route::post('/login', [AuthController::class, 'login']);        // Iniciar sesión
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');  // Cerrar sesión

Route::middleware('auth:api')->group( function()
{
    // Rutas de jugadores (sin restricción de rol)
    route::get('/players/ranking/winner', [PlayerController::class, 'showBest'])->name('players.showBest');
    route::put('/players/{id}', [PlayerController::class, 'update'])->name('players.update');
    route::post('/players/{id}/games/', [PlayerController::class, 'createDice'])->name('players.createDice');
    route::delete('/players/{id}/games', [PlayerController::class, 'deleteDice'])->name('players.deleteDice');
    route::get('/players/{id}/games', [PlayerController::class, 'showDice'])->name('players.showDice');

    // Rutas administrativas (middleware 'role:admin')
        Route::get('/players/ranking/loser', [AdminController::class, 'showWorst'])
        ->middleware('role:admin')
        ->name('players.showWorst');

        Route::get('/players/ranking/winner', [AdminController::class, 'showBest'])
        ->middleware('role:admin')
        ->name('players.showBest');
    
    Route::get('/players', [AdminController::class, 'showPlayers'])
        ->middleware('role:admin')
        ->name('players.showPlayers');
    
    Route::get('/players/ranking', [AdminController::class, 'showRanking'])
        ->middleware('role:admin')
        ->name('players.showRanking');


});