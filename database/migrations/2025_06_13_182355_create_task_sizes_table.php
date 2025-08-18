<?php
// database/migrations/2025_01_01_000007_create_task_sizes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskSizesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks_management.task_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // XS, S, M, L, XL, XXL
            $table->string('name', 50); // Очень маленькая, Маленькая, etc.
            $table->string('description'); // Подробное описание

            // Временные рамки
            $table->integer('min_hours')->nullable(); // Минимум часов
            $table->integer('max_hours')->nullable(); // Максимум часов
            $table->decimal('story_points', 3, 1)->nullable(); // Story points для agile

            // Настройки отображения
            $table->string('color', 7)->default('#6B7280'); // Hex цвет
            $table->string('icon', 50)->nullable(); // CSS класс иконки или emoji
            $table->integer('sort_order')->default(0);

            // Метаданные
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Дополнительные настройки

            $table->timestamps();
            $table->softDeletes();

            // Индексы
            $table->index('code');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tasks_management.task_sizes');
    }
}

