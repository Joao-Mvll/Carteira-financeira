<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\WalletController;
use Illuminate\Support\Facades\Route;

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

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::middleware(['guest', 'throttle:5,1'])->group(function () {

    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->name('login.attempt');

    Route::get('/register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'store'])
        ->name('register.store');
});
