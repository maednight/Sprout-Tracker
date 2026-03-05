<?php

use App\Http\Controllers\Auth\SproutAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('public.loading'))->name('loading');

Route::get('/login', fn () => view('public.home'))->name('login.view'); // your current login wrapper view
Route::post('/login', [SproutAuthController::class, 'login'])->name('login');

Route::get('/signup', fn () => view('public.signup'))->name('signup.view');
Route::post('/signup', [SproutAuthController::class, 'register'])->name('signup');

Route::get('/home', [SproutAuthController::class, 'home'])->name('home');
Route::get('/dashboard', fn () => view('public.dashboard'))->name('dashboard');
Route::get('/transactions', fn () => 'Transactions page')->name('transaction.index');
Route::get('/budget', fn () => 'Budget page')->name('budget.index');
Route::get('/savings', fn () => 'Savings page')->name('savings.index');
Route::get('/settings', fn () => 'Settings page')->name('settings.index');