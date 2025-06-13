<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskSize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📝 Создаю демонстрационные задачи...');

        // Получаем модели для связей
        $statuses = Status::pluck('id', 'slug');
        $sizes = TaskSize::pluck('id', 'code');
        $projects = Project::pluck('id', 'slug');

        // Проверяем что все необходимые данные есть
        if ($statuses->isEmpty() || $sizes->isEmpty() || $projects->isEmpty()) {
            $this->command->warn('⚠️ Не найдены необходимые данные (статусы, размеры или проекты). Пропускаю создание задач.');
            return;
        }

        $tasks = [
            [
                'name' => 'Исправить ошибку в форме логина',
                'description' => 'Пользователи не могут войти с правильными данными',
                'priority' => 'high',
                'size_id' => $sizes['S'] ?? null,
                'estimated_hours' => 2,
                'status_id' => $statuses['new'] ?? $statuses->first(),
                'project_id' => $projects['task-management-system'] ?? $projects->first(),
                'start_date' => now(),
                'due_date' => now()->addDays(1),
            ],
            [
                'name' => 'Добавить фильтрацию задач по размеру',
                'description' => 'Реализовать возможность фильтровать задачи по размеру в интерфейсе',
                'priority' => 'normal',
                'size_id' => $sizes['M'] ?? null,
                'estimated_hours' => 6,
                'status_id' => $statuses['in_progress'] ?? $statuses->first(),
                'project_id' => $projects['task-management-system'] ?? $projects->first(),
                'start_date' => now()->subDays(2),
                'due_date' => now()->addDays(3),
                'actual_hours' => 3.5,
            ],
            [
                'name' => 'Интеграция с Slack для уведомлений',
                'description' => 'Настроить отправку уведомлений о задачах в Slack каналы',
                'priority' => 'normal',
                'size_id' => $sizes['L'] ?? null,
                'estimated_hours' => 12,
                'status_id' => $statuses['assigned'] ?? $statuses->first(),
                'project_id' => $projects['api-integrations'] ?? $projects->first(),
                'start_date' => now()->addDays(1),
                'due_date' => now()->addWeek(),
            ],
            [
                'name' => 'Рефакторинг системы авторизации',
                'description' => 'Переписать устаревшую систему авторизации на современный стек',
                'priority' => 'low',
                'size_id' => $sizes['XL'] ?? null,
                'estimated_hours' => 30,
                'status_id' => $statuses['new'] ?? $statuses->first(),
                'project_id' => $projects['legacy-refactoring'] ?? $projects->first(),
                'start_date' => now()->addWeeks(2),
                'due_date' => now()->addMonth(),
            ],
            [
                'name' => 'Обновить документацию API',
                'description' => 'Актуализировать документацию после последних изменений',
                'priority' => 'normal',
                'size_id' => $sizes['XS'] ?? null,
                'estimated_hours' => 0.5,
                'status_id' => $statuses['done'] ?? $statuses->first(),
                'project_id' => $projects['api-integrations'] ?? $projects->first(),
                'start_date' => now()->subDays(3),
                'due_date' => now()->subDays(2),
                'completed_date' => now()->subDays(1),
                'actual_hours' => 0.75,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($tasks as $taskData) {
            // Проверяем есть ли пользователь с ID 1
            $userId = \App\Models\User::where('id', 1)->exists() ? 1 : null;

            if ($userId) {
                $taskData['created_by'] = $userId;
                $taskData['updated_by'] = $userId;
            }

            $task = Task::updateOrCreate(
                [
                    'name' => $taskData['name'],
                    'project_id' => $taskData['project_id']
                ], // Поиск по имени + проект
                $taskData // Данные для обновления/создания
            );

            if ($task->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Демонстрационные задачи: создано {$created}, обновлено {$updated}");
    }
}