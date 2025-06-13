<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskUserTable extends Migration
{
    public function up()
    {
        Schema::create('tasks_management.task_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')->constrained('tasks_management.tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('role', ['assignee', 'observer', 'reviewer', 'manager'])->default('assignee');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Уникальный индекс - один пользователь может иметь только одну роль на задаче
            $table->unique(['task_id', 'user_id', 'role', 'deleted_at'], 'task_user_role_unique');

            $table->index(['task_id', 'deleted_at']);
            $table->index(['user_id', 'role', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks_management.task_user');
    }
}