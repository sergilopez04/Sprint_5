<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RollController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
});
route::post('/user', [AuthController::class, 'register']);

Route::apiResource('rolls', RollController::class);
