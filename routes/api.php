<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

// --- ROTAS PÚBLICAS (Qualquer visitante pode aceder) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/dishes', [DishController::class, 'index']);
Route::get('/dishes/{dish}', [DishController::class, 'show']);

// --- ROTAS AUTENTICADAS (Requerem o Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil do Utilizador
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateMe']);

    // --- RESERVAS (Clientes e Admins) ---
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // --- ÁREA DE ADMINISTRAÇÃO (Apenas Proprietário) ---
    Route::middleware('is_admin')->group(function () {
        // Categorias
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Pratos
        Route::post('/dishes', [DishController::class, 'store']);
        Route::put('/dishes/{dish}', [DishController::class, 'update']);
        Route::delete('/dishes/{dish}', [DishController::class, 'destroy']);

        // Reservas (Ações exclusivas do Admin)
        Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    });
});