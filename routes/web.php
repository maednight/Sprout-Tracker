<?php

use App\Http\Controllers\Auth\SproutAuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
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

    /* Budget Routes */
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');

    Route::get('/savings', fn () => 'Savings page')->name('savings.index');

    /* Settings Routes */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/name', [SettingsController::class, 'updateName'])->name('settings.name.update');
    Route::post('/settings/photo', [SettingsController::class, 'updatePhoto'])->name('settings.photo.update');
    Route::put('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/logout', [SproutAuthController::class, 'logout'])->name('logout');

    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transaction.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction.store');

    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
});