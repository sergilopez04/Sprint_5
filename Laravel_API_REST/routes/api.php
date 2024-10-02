<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RollController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PlayerController;  
use App\Http\Controllers\AdminController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas para autenticación de usuarios
Route::post('/register', [AuthController::class, 'register']);  // Registro de usuarios
Route::post('/login', [AuthController::class, 'login']);        // Iniciar sesión
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');  // Cerrar sesión

// Rutas para los jugadores (solo usuarios autenticados)
Route::middleware('auth:api')->group(function () {

    // Crear un nuevo jugador/a
    Route::post('/players', [PlayerController::class, 'store']);

    // Modificar el nombre de un jugador/a
    Route::put('/players/{id}', [PlayerController::class, 'update']);

    // Ver todas las tiradas de un jugador/a específico
    Route::get('/players/{id}/games', [RollController::class, 'index']);

    // Un jugador/a realiza una tirada de dados
    Route::post('/players/{id}/games', [RollController::class, 'store']);

    // Eliminar todas las tiradas de un jugador/a
    Route::delete('/players/{id}/games', [RollController::class, 'destroy']);
    
    // Ver el ranking de todos los jugadores/as
    Route::get('/players/ranking', [PlayerController::class, 'ranking']);

    // Ver el jugador/a con peor porcentaje de éxito
    Route::get('/players/ranking/loser', [PlayerController::class, 'loser']);

    // Ver el jugador/a con mejor porcentaje de éxito
    Route::get('/players/ranking/winner', [PlayerController::class, 'winner']);
});

// Rutas para administración (solo accesibles por el admin)
Route::middleware(['auth:api', 'role:admin'])->group(function () {

    // Ver todos los jugadores/as y sus porcentajes de éxito
    Route::get('/admin/players', [AdminController::class, 'index']);

    // Ver el porcentaje de éxito promedio de todos los jugadores/as
    Route::get('/admin/players/average-success', [AdminController::class, 'averageSuccess']);
});