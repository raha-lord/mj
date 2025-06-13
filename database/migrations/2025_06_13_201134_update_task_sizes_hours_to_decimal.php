<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks_management.task_sizes', function (Blueprint $table) {
            // Изменяем тип полей с integer на decimal
            $table->decimal('min_hours', 5, 2)->nullable()->change();
            $table->decimal('max_hours', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks_management.task_sizes', function (Blueprint $table) {
            $table->integer('min_hours')->nullable()->change();
            $table->integer('max_hours')->nullable()->change();
        });
    }
};