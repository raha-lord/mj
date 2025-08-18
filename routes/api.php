<?php

use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API для работы с задачами
Route::middleware(['web', 'auth'])->prefix('tasks')->name('api.tasks.')->group(function () {

    // CRUD операции
    Route::get('/', [TaskApiController::class, 'index'])
        ->name('index');
    
    Route::post('/', [TaskApiController::class, 'store'])
        ->name('store');
    
    Route::get('/table-html', [TaskApiController::class, 'tableHtml'])
        ->name('table-html');

    // Работа с временем
    Route::post('{task}/log-time', [TaskApiController::class, 'logTime'])
        ->name('log-time');

    // Управление исполнителями
    Route::post('{task}/assignees', [TaskApiController::class, 'addAssignee'])
        ->name('add-assignee');

    Route::delete('{task}/assignees/{user}', [TaskApiController::class, 'removeAssignee'])
        ->name('remove-assignee');

    Route::patch('{task}/assignees/{user}/role', [TaskApiController::class, 'changeAssigneeRole'])
        ->name('change-assignee-role');

    Route::post('{task}/auto-assign', [TaskApiController::class, 'autoAssign'])
        ->name('auto-assign');

    // Управление статусом
    Route::patch('{task}/complete', [TaskApiController::class, 'markCompleted'])
        ->name('complete');

    Route::patch('{task}/reopen', [TaskApiController::class, 'reopen'])
        ->name('reopen');

    // Утилиты и статистика
    Route::get('size-recommendation', [TaskApiController::class, 'getSizeRecommendation'])
        ->name('size-recommendation');

    Route::get('size-stats', [TaskApiController::class, 'sizeStats'])
        ->name('size-stats');
});

Route::middleware(['web', 'auth'])->group(function () {
    // Записи времени
    Route::get('tasks/{task}/time-logs', [TimeLogController::class, 'index']);
    Route::post('tasks/{task}/time-logs', [TimeLogController::class, 'store']);
    Route::put('time-logs/{timeLog}', [TimeLogController::class, 'update']);
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy']);
    Route::post('time-logs/{timeLogId}/restore', [TimeLogController::class, 'restore']);
    Route::get('tasks/{task}/time-logs/deleted', [TimeLogController::class, 'deleted']);
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('api.time-logs.destroy');
    Route::put('time-logs/{timeLog}', [TimeLogController::class, 'update'])->name('api.time-logs.update');
});