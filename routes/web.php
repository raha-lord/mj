<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (без авторизации)
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome');

// Роуты авторизации (login, register, etc.)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Protected Routes (требуют авторизации)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Главная страница
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Профиль пользователя
    Route::view('profile', 'profile')->name('profile');

    // Все CRUD роуты
    Route::resource('status', StatusController::class);
    Route::resource('project', ProjectController::class);
    Route::resource('task', TaskController::class);

    // Добавьте сюда все остальные защищенные роуты
});