<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

require __DIR__.'/admin_routes.php';
require __DIR__.'/group_admin_routes.php';
require __DIR__.'/candidate_routes.php';
require __DIR__.'/relative_routes.php';

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/auth/google', [GoogleAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('auth.google.login');
    Route::post('/auth/google', [GoogleAuthController::class, 'signup'])
        ->middleware('throttle:5,1')
        ->name('auth.google.signup');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
