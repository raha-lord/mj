<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks_management.organization_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['super_user', 'org_admin', 'project_manager', 'member']);
            $table->timestamp('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Аудит поля
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            // Уникальный индекс - пользователь может быть только один раз в организации
            $table->unique(['organization_id', 'user_id', 'deleted_at'], 'org_user_unique');
            
            // Индексы для быстрого поиска
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('role');
            $table->index('deleted_at');
            
            // Foreign keys
            $table->foreign('organization_id')->references('id')->on('tasks_management.organizations');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_management.organization_user');
    }
};