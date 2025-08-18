<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Защищенные маршруты (требуют авторизации)
Route::middleware(['auth'])->group(function () {

    // Основные CRUD ресурсы
    Route::resource('statuses', StatusController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);

    // Дополнительные web-маршруты для задач
    Route::prefix('tasks')->name('tasks.')->group(function () {
        // Действия, которые выполняются через формы (POST/PATCH запросы)
        Route::post('{task}/log-time', [TaskController::class, 'logTime'])
            ->name('log-time');

        Route::patch('{task}/complete', [TaskController::class, 'markCompleted'])
            ->name('complete');

        Route::patch('{task}/reopen', [TaskController::class, 'reopen'])
            ->name('reopen');

        // AJAX эндпоинты для получения данных
        Route::get('size-recommendation', [TaskController::class, 'getSizeRecommendation'])
            ->name('size-recommendation');
    });
});

Route::post('tasks/{task}/log-time', [TaskController::class, 'logTime'])->name('tasks.log-time');