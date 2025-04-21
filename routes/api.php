<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PedidosController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
// Route::get('/login', function () {
//     return response()->json(['message' => 'Página de login']);
// })->name('login');
Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('ver-usuario', [AuthController::class, 'verUsuario']);
    //Pedidos
    Route::get('pedidos', [PedidosController::class, 'index']);
    Route::post('pedidos', [PedidosController::class, 'adicionar']);
    Route::get('pedidos/{id}', [PedidosController::class, 'visualizar']);
    Route::put('pedidos/{id}', [PedidosController::class, 'editar']);
    Route::put('pedidos/cancelar/{id}', [PedidosController::class, 'cancelar']);
});