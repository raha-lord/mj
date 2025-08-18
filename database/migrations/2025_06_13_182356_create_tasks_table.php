<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks_management.tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Размер задачи
            $table->foreignId('size_id')
                ->nullable()
                ->constrained('tasks_management.task_sizes')
                ->onDelete('set null');

            // Даты
            $table->timestamp('start_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_date')->nullable();

            // Время выполнения
            $table->decimal('actual_hours', 5, 2)
                ->nullable()
                ->comment('Фактически потраченное время в часах');

            $table->decimal('estimated_hours', 5, 2)
                ->nullable()
                ->comment('Оценочное время в часах');

            // Связи
            $table->foreignId('status_id')->constrained('tasks_management.statuses');
            $table->foreignId('project_id')->nullable()->constrained('tasks_management.projects')->onDelete('set null');

            // Аудит поля
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Индексы
            $table->index(['status_id', 'deleted_at']);
            $table->index(['project_id', 'deleted_at']);
            $table->index(['priority', 'due_date']);
            $table->index(['created_by', 'deleted_at']);
            $table->index(['size_id', 'status_id']);
            $table->index('size_id');
            $table->index('start_date');
            $table->index('due_date');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tasks_management.tasks');
    }
}