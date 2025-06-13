<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditTables extends Migration
{
    public function up()
    {
        // История изменений задач
        Schema::create('audit.task_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks_management.tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('field'); // какое поле изменилось
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('metadata')->nullable(); // дополнительные данные

            $table->timestamp('changed_at');

            $table->index(['task_id', 'changed_at']);
            $table->index(['user_id', 'changed_at']);
            $table->index('field');
        });

        // Общий лог активности пользователей
        Schema::create('audit.user_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('action'); // login, logout, task_created, etc.
            $table->string('entity_type')->nullable(); // Task, Project, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('data')->nullable(); // дополнительные данные
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit.user_activity');
        Schema::dropIfExists('audit.task_history');
    }
}
