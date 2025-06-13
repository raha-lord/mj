<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Начинаю выполнение сидов...');

        // Порядок важен из-за внешних ключей
        $this->call([
            TaskSizesSeeder::class,      // Сначала размеры (независимые)
            StatusesSeeder::class,       // Затем статусы (независимые)
            ProjectsSeeder::class,       // Затем проекты (независимые)
            TasksSeeder::class,          // Задачи (зависят от всех предыдущих)
        ]);

        $this->command->info('✅ Все сиды выполнены успешно!');
    }
}