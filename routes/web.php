<?php

use App\Http\Controllers\Auth\SproutAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/* Public Route */
Route::get('/', fn () => view('public.loading'))->name('loading');

/* Guest Routes */
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('public.home'))->name('login.view');
    Route::post('/login', [SproutAuthController::class, 'login'])->name('login');

    Route::get('/signup', fn () => view('public.signup'))->name('signup.view');
    Route::post('/signup', [SproutAuthController::class, 'register'])->name('signup');
});

/* Authenticated Routes */
Route::middleware('auth')->group(function () {
    Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions', fn () => 'Transactions page')->name('transaction.index');
    Route::get('/budget', fn () => 'Budget page')->name('budget.index');
    Route::get('/savings', fn () => 'Savings page')->name('savings.index');
    Route::get('/settings', fn () => 'Settings page')->name('settings.index');

    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transaction.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction.store');

    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
});