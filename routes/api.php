<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/wallet', [WalletController::class, 'balance']);
    Route::post('/deposit', [WalletController::class, 'deposit']);
    Route::post('/transfer', [WalletController::class, 'transfer']);

    Route::get('/statement', [TransactionController::class, 'index']);
    Route::post('/transactions/{transaction}/reverse', [TransactionController::class, 'reverse']);
});
