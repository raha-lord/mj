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

Route::redirect('/', '/dashboard');

Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile route handled by auth.php

require __DIR__.'/auth.php';

// Защищенные маршруты (требуют авторизации)
Route::middleware(['auth'])->group(function () {

    // Основные CRUD ресурсы
    Route::resource('statuses', StatusController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);

    // Vue версия страницы задач с Headless UI
    Route::get('tasks-vue', [TaskController::class, 'indexVue'])
        ->name('tasks.vue');

    // Демо страница для сравнения реализаций
    Route::view('demo', 'demo')->name('demo');

    // Тестовая страница для отладки модалок
    Route::view('test-modal', 'test-modal')->name('test-modal');

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