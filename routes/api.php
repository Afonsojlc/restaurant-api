<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (qualquer pessoa pode aceder sem estar logada)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas autenticadas (o cliente tem de mostrar a "pulseira VIP" / token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Gestão do Perfil
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);

    // (As rotas do menu e das reservas vão ser adicionadas aqui dentro nas próximas fases)

});