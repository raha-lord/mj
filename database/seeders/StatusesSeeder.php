<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Создаю статусы задач...');

        $statuses = [
            [
                'slug' => 'new',
                'name' => 'Новая',
                'description' => 'Новая задача, ожидает назначения',
                'color' => '#6B7280',
                'sort_order' => 10,
                'is_final' => false,
            ],
            [
                'slug' => 'assigned',
                'name' => 'Назначена',
                'description' => 'Задача назначена исполнителю',
                'color' => '#3B82F6',
                'sort_order' => 20,
                'is_final' => false,
            ],
            [
                'slug' => 'in_progress',
                'name' => 'В работе',
                'description' => 'Задача выполняется',
                'color' => '#F59E0B',
                'sort_order' => 30,
                'is_final' => false,
            ],
            [
                'slug' => 'code_review',
                'name' => 'На ревью',
                'description' => 'Код-ревью или проверка результата',
                'color' => '#8B5CF6',
                'sort_order' => 40,
                'is_final' => false,
            ],
            [
                'slug' => 'testing',
                'name' => 'Тестирование',
                'description' => 'Задача на тестировании',
                'color' => '#06B6D4',
                'sort_order' => 50,
                'is_final' => false,
            ],
            [
                'slug' => 'done',
                'name' => 'Выполнена',
                'description' => 'Задача успешно завершена',
                'color' => '#10B981',
                'sort_order' => 60,
                'is_final' => true,
            ],
            [
                'slug' => 'cancelled',
                'name' => 'Отменена',
                'description' => 'Задача отменена',
                'color' => '#EF4444',
                'sort_order' => 70,
                'is_final' => true,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($statuses as $statusData) {
            $status = Status::updateOrCreate(
                ['slug' => $statusData['slug']], // Поиск по slug
                $statusData // Данные для обновления/создания
            );

            if ($status->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Статусы: создано {$created}, обновлено {$updated}");
    }
}