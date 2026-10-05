<?php

use App\Http\Controllers\RoomController;
use  App\Http\Controllers\ReserveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rota padrão do laravel
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas da api do quarto(room)

// Rota para mostrar os quartos
Route::get('rooms', [RoomController::class, 'index']);

// Rota para criar um quarto
Route::post('rooms', [RoomController::class, 'store']);

// Rota para ver informações de um quarto específico
Route::get('rooms/{id}', [RoomController::class, 'show']);

// Rota para atualizar dados de um quarto específico
Route::put('rooms/{id}', [RoomController::class, 'update']);

// Rota para deletar um quarto especifíco
Route::delete('rooms/{id}', [RoomController::class, 'destroy']);

// Rotas da api de reservas (reserves)

// Rota para mostrar as reservas
Route::get('reserves', [ReserveController::class, 'index']);

// Rota para adicionar uma reserva
Route::post('reserves', [ReserveController::class, 'store']);