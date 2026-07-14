<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\WalletController;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('/deposit', [WalletController::class, 'deposit'])
        ->name('wallet.deposit');

    Route::post('/deposit', [WalletController::class, 'storeDeposit'])
        ->name('wallet.deposit.store');

    Route::get('/transfer', [WalletController::class, 'transfer'])
        ->name('wallet.transfer');

    Route::post('/transfer', [WalletController::class, 'storeTransfer'])
        ->name('wallet.transfer.store');

    Route::get('/statement', [TransactionController::class, 'index'])
        ->name('wallet.statement');

    Route::post('/transactions/{transaction}/reverse', [TransactionController::class, 'reverse'])
        ->name('wallet.reverse');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->name('login.attempt');

    Route::get('/register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'store'])
        ->name('register.store');
});
