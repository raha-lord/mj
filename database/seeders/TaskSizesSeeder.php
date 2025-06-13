<?php

namespace Database\Seeders;

use App\Models\TaskSize;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSizesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎯 Создаю размеры задач...');

        $sizes = [
            [
                'code' => 'XS',
                'name' => 'Очень маленькая',
                'description' => 'Быстрые задачи: исправление опечаток, мелкие правки, простые конфигурации',
                'min_hours' => 0,
                'max_hours' => 1,
                'story_points' => 0.5,
                'color' => '#10B981',
                'icon' => '🔸',
                'sort_order' => 10,
                'metadata' => [
                    'examples' => ['Исправить опечатку', 'Поменять цвет кнопки', 'Обновить текст'],
                    'complexity' => 'trivial'
                ]
            ],
            [
                'code' => 'S',
                'name' => 'Маленькая',
                'description' => 'Небольшие задачи: простые функции, базовые формы, мелкие доработки',
                'min_hours' => 1,
                'max_hours' => 4,
                'story_points' => 1,
                'color' => '#3B82F6',
                'icon' => '🔹',
                'sort_order' => 20,
                'metadata' => [
                    'examples' => ['Добавить простую валидацию', 'Создать базовую форму', 'Мелкий баг'],
                    'complexity' => 'simple'
                ]
            ],
            [
                'code' => 'M',
                'name' => 'Средняя',
                'description' => 'Стандартные задачи: новые страницы, CRUD операции, интеграции API',
                'min_hours' => 4,
                'max_hours' => 8,
                'story_points' => 2,
                'color' => '#F59E0B',
                'icon' => '🔶',
                'sort_order' => 30,
                'metadata' => [
                    'examples' => ['Создать новую страницу', 'CRUD для сущности', 'Интеграция с API'],
                    'complexity' => 'medium'
                ]
            ],
            [
                'code' => 'L',
                'name' => 'Большая',
                'description' => 'Сложные задачи: комплексная логика, архитектурные изменения, большие функции',
                'min_hours' => 8,
                'max_hours' => 16,
                'story_points' => 3,
                'color' => '#EF4444',
                'icon' => '🔺',
                'sort_order' => 40,
                'metadata' => [
                    'examples' => ['Новый модуль', 'Сложная бизнес-логика', 'Рефакторинг архитектуры'],
                    'complexity' => 'complex'
                ]
            ],
            [
                'code' => 'XL',
                'name' => 'Очень большая',
                'description' => 'Масштабные задачи: новые системы, major features, глобальные изменения',
                'min_hours' => 16,
                'max_hours' => 40,
                'story_points' => 5,
                'color' => '#7C3AED',
                'icon' => '🔻',
                'sort_order' => 50,
                'metadata' => [
                    'examples' => ['Новая подсистема', 'Интеграция с внешней системой', 'Масштабный рефакторинг'],
                    'complexity' => 'very_complex'
                ]
            ],
            [
                'code' => 'XXL',
                'name' => 'Эпик',
                'description' => 'Эпические задачи: требуют разбиения на подзадачи, длительная разработка',
                'min_hours' => 40,
                'max_hours' => null,
                'story_points' => 8,
                'color' => '#1F2937',
                'icon' => '⭐',
                'sort_order' => 60,
                'metadata' => [
                    'examples' => ['Новый продукт', 'Переписывание системы', 'Интеграция нескольких систем'],
                    'complexity' => 'epic',
                    'should_be_split' => true
                ]
            ]
        ];

        $created = 0;
        $updated = 0;

        foreach ($sizes as $sizeData) {
            $size = TaskSize::updateOrCreate(
                ['code' => $sizeData['code']], // Поиск по коду
                $sizeData // Данные для обновления/создания
            );

            if ($size->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command->info("✅ Размеры задач: создано {$created}, обновлено {$updated}");

        if ($created > 0) {
            $this->command->table(
                ['Код', 'Название', 'Время (ч)', 'SP'],
                collect($sizes)->map(fn($s) => [
                    $s['code'],
                    $s['name'],
                    ($s['max_hours'] ? "{$s['min_hours']}-{$s['max_hours']}" : "{$s['min_hours']}+"),
                    $s['story_points']
                ])
            );
        }
    }
}