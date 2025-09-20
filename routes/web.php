<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SetPasswordController;
use App\Http\Controllers\OrganizationSelectionController;
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

// Главная страница с проверкой организации
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    
    $user = auth()->user();
    
    // Проверяем нужно ли установить пароль
    if (!$user->hasPassword()) {
        return redirect()->route('set-password');
    }
    
    // Проверяем есть ли у пользователя организации
    if ($user->organizations()->count() === 0) {
        // Проверяем есть ли приглашения
        $hasInvitations = \App\Models\OrganizationInvitation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
            
        if ($hasInvitations) {
            return redirect()->route('organization-selection');
        }
    }
    
    return redirect()->route('dashboard');
});

// Установка пароля (доступно без organization middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('set-password', [SetPasswordController::class, 'show'])->name('set-password');
    Route::post('set-password', [SetPasswordController::class, 'store'])->name('set-password.store');
});

// Выбор организации для пользователей без организации
Route::middleware(['auth'])->group(function () {
    Route::get('organization-selection', [OrganizationSelectionController::class, 'index'])->name('organization-selection');
});


Route::get('dashboard', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth', 'verified', 'organization:member'])
    ->name('dashboard');

// Управление организациями (доступно всем авторизованным)
Route::middleware(['auth'])->group(function () {
    Route::get('organizations/select', [App\Http\Controllers\OrganizationController::class, 'select'])->name('organizations.select');
    Route::post('organizations/{organization}/switch', [App\Http\Controllers\OrganizationController::class, 'webSwitch'])->name('organizations.switch');
    Route::get('organizations', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('organizations', [App\Http\Controllers\OrganizationController::class, 'webStore'])->name('organizations.store');
    Route::get('organizations/{organization}', [App\Http\Controllers\OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('organizations/{organization}/settings', [App\Http\Controllers\OrganizationController::class, 'settings'])->name('organizations.settings');
    Route::get('organizations/{organization}/members', [App\Http\Controllers\OrganizationController::class, 'members'])->name('organizations.members');
});

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