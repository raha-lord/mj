<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SetPasswordController;
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

// Установка пароля (доступно без organization middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('set-password', [SetPasswordController::class, 'show'])->name('set-password');
    Route::post('set-password', [SetPasswordController::class, 'store'])->name('set-password.store');
});

Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified', 'organization:member'])
    ->name('dashboard');

// Profile route handled by auth.php

require __DIR__.'/auth.php';

// Защищенные маршруты (требуют членство в организации)
Route::middleware(['auth', 'organization:member'])->group(function () {

    // Основные CRUD ресурсы (просмотр доступен всем участникам)
    Route::resource('statuses', StatusController::class)->only(['index', 'show']);
    Route::resource('projects', ProjectController::class)->only(['index', 'show']);
    Route::resource('tasks', TaskController::class)->only(['index', 'show']);

    // Vue версия страницы задач с Headless UI
    Route::get('tasks-vue', [TaskController::class, 'indexVue'])
        ->name('tasks.vue');

    // Демо страница для сравнения реализаций
    Route::view('demo', 'demo')->name('demo');

    // Тестовая страница для отладки модалок
    Route::view('test-modal', 'test-modal')->name('test-modal');

    // Дополнительные web-маршруты для задач (все участники)
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

// Создание и редактирование (проект-менеджеры и админы)
Route::middleware(['auth', 'organization:project_manager,org_admin'])->group(function () {
    // CRUD операции создания и редактирования
    Route::resource('statuses', StatusController::class)->except(['index', 'show']);
    Route::resource('projects', ProjectController::class)->except(['index', 'show']);
    Route::resource('tasks', TaskController::class)->except(['index', 'show']);
});