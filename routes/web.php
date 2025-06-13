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

    // Статусы
    Route::resource('statuses', StatusController::class);

    // Проекты
    Route::resource('projects', ProjectController::class);

    // Задачи
    Route::resource('tasks', TaskController::class);

    // Дополнительные маршруты для задач
    Route::prefix('tasks')->name('tasks.')->group(function () {
        // Логирование времени
        Route::post('{task}/log-time', [TaskController::class, 'logTime'])->name('log-time');

        // Управление исполнителями
        Route::post('{task}/assignees', [TaskController::class, 'addAssignee'])->name('assignees.store');
        Route::delete('{task}/assignees/{user}', [TaskController::class, 'removeAssignee'])->name('assignees.destroy');

        // Изменение статуса задачи
        Route::post('{task}/mark-completed', [TaskController::class, 'markCompleted'])->name('mark-completed');
        Route::post('{task}/reopen', [TaskController::class, 'reopen'])->name('reopen');

        // Статистика
        Route::get('size-stats', [TaskController::class, 'sizeStats'])->name('size-stats');
    });

    // API маршруты для AJAX запросов
    Route::prefix('api')->name('api.')->group(function () {
        // Рекомендация размера задачи
        Route::get('tasks/size-recommendation', [TaskController::class, 'getSizeRecommendation'])->name('tasks.size-recommendation');
    });

    // Альтернативный маршрут для рекомендации размера (без api prefix)
    Route::get('tasks/size-recommendation', [TaskController::class, 'getSizeRecommendation'])->name('tasks.size-recommendation');
});