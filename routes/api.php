<?php

use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationUserController;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganizationInvitationController;
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

// API для работы с задачами (требует членство в организации)
Route::middleware(['web', 'auth', 'organization:member'])->prefix('tasks')->name('api.tasks.')->group(function () {

    // CRUD операции (чтение доступно всем участникам)
    Route::get('/', [TaskApiController::class, 'index'])
        ->name('index');
        
    Route::get('/table-html', [TaskApiController::class, 'tableHtml'])
        ->name('table-html');
        
    Route::get('/{task}', [TaskApiController::class, 'show'])
        ->name('show');

    // Утилиты и статистика (доступно всем участникам)
    Route::get('size-recommendation', [TaskApiController::class, 'getSizeRecommendation'])
        ->name('size-recommendation');

    Route::get('size-stats', [TaskApiController::class, 'sizeStats'])
        ->name('size-stats');
});

// Создание и редактирование задач (проект-менеджеры и админы)
Route::middleware(['web', 'auth', 'organization:project_manager,org_admin'])->prefix('tasks')->name('api.tasks.')->group(function () {
    Route::post('/', [TaskApiController::class, 'store'])
        ->name('store');
        
    Route::put('/{task}', [TaskApiController::class, 'update'])
        ->name('update');

    // Управление исполнителями
    Route::post('{task}/assignees', [TaskApiController::class, 'addAssignee'])
        ->name('add-assignee');

    Route::delete('{task}/assignees/{user}', [TaskApiController::class, 'removeAssignee'])
        ->name('remove-assignee');

    Route::patch('{task}/assignees/{user}/role', [TaskApiController::class, 'changeAssigneeRole'])
        ->name('change-assignee-role');

    Route::post('{task}/auto-assign', [TaskApiController::class, 'autoAssign'])
        ->name('auto-assign');
});

// Работа с задачами (все участники могут логировать время и менять статус)
Route::middleware(['web', 'auth', 'organization:member'])->prefix('tasks')->name('api.tasks.')->group(function () {
    // Работа с временем
    Route::post('{task}/log-time', [TaskApiController::class, 'logTime'])
        ->name('log-time');

    // Управление статусом
    Route::patch('{task}/complete', [TaskApiController::class, 'markCompleted'])
        ->name('complete');

    Route::patch('{task}/reopen', [TaskApiController::class, 'reopen'])
        ->name('reopen');
});

// Записи времени (требует членство в организации)
Route::middleware(['web', 'auth', 'organization:member'])->group(function () {
    // Просмотр записей времени (доступно всем участникам)
    Route::get('tasks/{task}/time-logs', [TimeLogController::class, 'index']);
    Route::get('tasks/{task}/time-logs/deleted', [TimeLogController::class, 'deleted']);
    
    // Создание записей времени (все участники могут логировать время)
    Route::post('tasks/{task}/time-logs', [TimeLogController::class, 'store']);
    
    // Редактирование и удаление (участники могут редактировать свои записи)
    Route::put('time-logs/{timeLog}', [TimeLogController::class, 'update'])->name('api.time-logs.update');
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('api.time-logs.destroy');
    Route::post('time-logs/{timeLogId}/restore', [TimeLogController::class, 'restore']);
});

// =============================================================================
// ORGANIZATIONS API ROUTES
// =============================================================================

// Установка пароля (доступно без organization middleware)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('set-password', [SetPasswordController::class, 'show'])->name('set-password');
    Route::post('set-password', [SetPasswordController::class, 'store'])->name('set-password.store');
    Route::get('password-status', [SetPasswordController::class, 'status'])->name('password.status');
});

// Базовые маршруты организаций (доступно всем авторизованным пользователям)
Route::middleware(['web', 'auth.api'])->group(function () {
    // Список организаций пользователя
    Route::get('organizations', [OrganizationController::class, 'apiIndex'])->name('api.organizations.index');
    
    // Создание организации (доступно всем для самостоятельной регистрации)
    Route::post('organizations', [OrganizationController::class, 'store'])->name('api.organizations.store');
    
    // Переключение организации
    Route::post('organizations/switch', [OrganizationController::class, 'switch'])->name('api.organizations.switch');
    
    // Получение контекста текущей организации
    Route::get('organizations/context', [OrganizationController::class, 'context'])->name('api.organizations.context');
    
    // Поиск организаций
    Route::get('organizations/search', [OrganizationController::class, 'search'])->name('api.organizations.search');
    
    // Поиск пользователей для приглашений
    Route::get('users/search', [UserController::class, 'search'])->name('api.users.search');
    
    // Проверка email для приглашений
    Route::get('users/check-email', [UserController::class, 'checkEmail'])->name('api.users.check-email');
    
    // Приглашения организаций
    Route::get('invitations', [OrganizationInvitationController::class, 'index'])->name('api.invitations.index');
    Route::post('invitations/{invitation}/accept', [OrganizationInvitationController::class, 'accept'])->name('api.invitations.accept');
    Route::post('invitations/{invitation}/decline', [OrganizationInvitationController::class, 'decline'])->name('api.invitations.decline');
});

// Управление конкретной организацией (требует доступ к организации)
Route::middleware(['web', 'auth', 'organization:member'])->group(function () {
    // Просмотр деталей организации
    Route::get('organizations/{organization}', [OrganizationController::class, 'apiShow'])->name('api.organizations.show');
});

// Управление организацией (только админы и супер пользователи)
Route::middleware(['web', 'auth', 'organization:org_admin,super_user'])->group(function () {
    // Обновление организации
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('api.organizations.update');
    Route::patch('organizations/{organization}', [OrganizationController::class, 'update'])->name('api.organizations.patch');
});

// Удаление организации (только супер пользователи)
Route::middleware(['web', 'auth', 'organization:super_user'])->group(function () {
    Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('api.organizations.destroy');
});

// Управление участниками организации
Route::middleware(['web', 'auth', 'organization:member'])->group(function () {
    // Просмотр участников (доступно всем участникам)
    Route::get('organizations/{organization}/users', [OrganizationUserController::class, 'index'])->name('api.organizations.users.index');
    
    // Статистика приглашений (доступно всем участникам)
    Route::get('organizations/{organization}/users/stats', [OrganizationUserController::class, 'stats'])->name('api.organizations.users.stats');
});

// Приглашение и управление участниками (админы и проект-менеджеры)
Route::middleware(['web', 'auth', 'organization:org_admin,project_manager'])->group(function () {
    // Приглашение пользователя
    Route::post('organizations/{organization}/users', [OrganizationUserController::class, 'store'])->name('api.organizations.users.store');
    
    // Массовое приглашение
    Route::post('organizations/{organization}/users/bulk-invite', [OrganizationUserController::class, 'bulkInvite'])->name('api.organizations.users.bulk-invite');
});

// Управление ролями и удаление участников (только админы)
Route::middleware(['web', 'auth', 'organization:org_admin'])->group(function () {
    // Обновление роли участника
    Route::put('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'update'])->name('api.organizations.users.update');
    Route::patch('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'update'])->name('api.organizations.users.patch');
    
    // Удаление участника
    Route::delete('organizations/{organization}/users/{user}', [OrganizationUserController::class, 'destroy'])->name('api.organizations.users.destroy');
    
    // Отзыв приглашения
    Route::post('organizations/{organization}/users/{user}/revoke', [OrganizationUserController::class, 'revokeInvitation'])->name('api.organizations.users.revoke');
    
    // Отзыв приглашения через invitation ID
    Route::post('invitations/{invitation}/revoke', [OrganizationInvitationController::class, 'revoke'])->name('api.invitations.revoke');
});