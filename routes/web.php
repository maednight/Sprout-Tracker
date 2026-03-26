<?php

use App\Http\Controllers\Auth\SproutAuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/* Public Route */
Route::get('/', fn () => view('public.loading'))->name('loading');

/* Guest Routes */
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('public.home'))->name('login.view');
    Route::post('/login', [SproutAuthController::class, 'login'])->name('login');
    Route::get('/forgot-password', [SproutAuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [SproutAuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [SproutAuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [SproutAuthController::class, 'updateForgottenPassword'])->name('password.update');

    Route::get('/signup', fn () => view('public.signup'))->name('signup.view');
    Route::post('/signup', [SproutAuthController::class, 'register'])->name('signup');
});

/* Authenticated Routes */
Route::middleware('auth')->group(function () {
    Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.index');

    /* Budget Routes */
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
    Route::get('/budget/{budget}/allocate', [BudgetController::class, 'allocate'])->name('budget.allocate');
    Route::put('/budget/{budget}/allocate', [BudgetController::class, 'updateAllocation'])->name('budget.allocate.update');
    Route::delete('/budget/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::post('/budget/{budget}/revert-override', [BudgetController::class, 'revertOverride'])->name('budget.override.revert');

    Route::get('/savings', [SavingsController::class, 'index'])->name('savings.index');
    Route::get('/savings/transfer/create', [SavingsController::class, 'createTransfer'])->name('savings.transfer.create');
    Route::post('/savings/transfer', [SavingsController::class, 'transfer'])->name('savings.transfer');
    Route::get('/savings/transfer/{savingsTransfer}/edit', [SavingsController::class, 'editTransfer'])->name('savings.transfer.edit');
    Route::put('/savings/transfer/{savingsTransfer}', [SavingsController::class, 'updateTransfer'])->name('savings.transfer.update');
    Route::delete('/savings/transfer/{savingsTransfer}', [SavingsController::class, 'destroyTransfer'])->name('savings.transfer.destroy');

    /* Settings Routes */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/name', [SettingsController::class, 'updateName'])->name('settings.name.update');
    Route::post('/settings/photo', [SettingsController::class, 'updatePhoto'])->name('settings.photo.update');
    Route::delete('/settings/photo', [SettingsController::class, 'destroyPhoto'])->name('settings.photo.destroy');
    Route::put('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/logout', [SproutAuthController::class, 'logout'])->name('logout');

    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transaction.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transaction.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
});
