<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📂 Создаю проекты...');

        $projects = [
            [
                'name' => 'Система управления задачами',
                'slug' => 'task-management-system',
                'description' => 'Основной проект для управления задачами и проектами команды',
            ],
            [
                'name' => 'Мобильное приложение',
                'slug' => 'mobile-app',
                'description' => 'Разработка мобильного приложения для iOS и Android',
            ],
            [
                'name' => 'API интеграции',
                'slug' => 'api-integrations',
                'description' => 'Интеграция с внешними сервисами и API',
            ],
            [
                'name' => 'Рефакторинг legacy кода',
                'slug' => 'legacy-refactoring',
                'description' => 'Модернизация и рефакторинг устаревшего кода',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($projects as $projectData) {
            // Проверяем есть ли пользователь с ID 1, если нет - не указываем created_by
            $userId = \App\Models\User::where('id', 1)->exists() ? 1 : null;

            if ($userId) {
                $projectData['created_by'] = $userId;
                $projectData['updated_by'] = $userId;
            }

            $project = Project::updateOrCreate(
                ['slug' => $projectData['slug']], // Поиск по slug
                $projectData // Данные для обновления/создания
            );

            if ($project->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Проекты: создано {$created}, обновлено {$updated}");
    }
}