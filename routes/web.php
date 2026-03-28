<?php

use App\Http\Controllers\Auth\SproutAuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/* Public Route */
Route::get('/', fn () => view('public.shared.loading'))->name('loading');

/* Guest Routes */
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('public.auth.home'))->name('login_view');
    Route::post('/login', [SproutAuthController::class, 'login'])->name('login');
    Route::get('/forgot-password', [SproutAuthController::class, 'forgotPassword'])->name('password_request');
    Route::post('/forgot-password', [SproutAuthController::class, 'sendResetLink'])->name('password_email');
    Route::get('/reset-password/{token}', [SproutAuthController::class, 'resetPassword'])->name('password_reset');
    Route::post('/reset-password', [SproutAuthController::class, 'updateForgottenPassword'])->name('password_update');

    Route::get('/signup', fn () => view('public.auth.signup'))->name('signup_view');
    Route::post('/signup', [SproutAuthController::class, 'register'])->name('signup');
});

/* Authenticated Routes */
Route::middleware('auth')->group(function () {
    Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction_index');

    /* Budget Routes */
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget_index');
    Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget_create');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget_store');
    Route::get('/budget/{budget}/allocate', [BudgetController::class, 'allocate'])->name('budget_allocate');
    Route::put('/budget/{budget}/allocate', [BudgetController::class, 'updateAllocation'])->name('budget_allocate_update');
    Route::delete('/budget/{budget}', [BudgetController::class, 'destroy'])->name('budget_destroy');
    Route::post('/budget/{budget}/revert-override', [BudgetController::class, 'revertOverride'])->name('budget_override_revert');

    Route::get('/savings', [SavingsController::class, 'index'])->name('savings_index');
    Route::get('/savings/transfer/create', [SavingsController::class, 'createTransfer'])->name('savings_transfer_create');
    Route::post('/savings/transfer', [SavingsController::class, 'transfer'])->name('savings_transfer');
    Route::get('/savings/transfer/{savingsTransfer}/edit', [SavingsController::class, 'editTransfer'])->name('savings_transfer_edit');
    Route::put('/savings/transfer/{savingsTransfer}', [SavingsController::class, 'updateTransfer'])->name('savings_transfer_update');
    Route::delete('/savings/transfer/{savingsTransfer}', [SavingsController::class, 'destroyTransfer'])->name('savings_transfer_destroy');

    /* Settings Routes */
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings_index');
    Route::put('/settings/name', [SettingsController::class, 'updateName'])->name('settings_name_update');
    Route::post('/settings/photo', [SettingsController::class, 'updatePhoto'])->name('settings_photo_update');
    Route::delete('/settings/photo', [SettingsController::class, 'destroyPhoto'])->name('settings_photo_destroy');
    Route::put('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings_email_update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings_password_update');
    Route::post('/logout', [SproutAuthController::class, 'logout'])->name('logout');

    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transaction_create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transaction_store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transaction_edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transaction_update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transaction_destroy');
});
