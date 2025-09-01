<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])
        ->name('login');
    
    Route::post('login', [AuthController::class, 'login']);

    Route::get('register', [AuthController::class, 'showRegister'])
        ->name('register');
    
    Route::post('register', [AuthController::class, 'register']);

    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Profile routes
    Route::get('profile', [ProfileController::class, 'show'])
        ->name('profile.show');
    
    Route::get('profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    
    Route::patch('profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    
    Route::delete('profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});
