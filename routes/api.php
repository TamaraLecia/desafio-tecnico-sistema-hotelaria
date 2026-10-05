<?php

use App\Http\Controllers\RoomController;
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
