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
        Schema::create('tasks_management.organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('invited_by');
            $table->enum('role', ['member', 'project_manager', 'org_admin']);
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired']);
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            // Уникальный индекс - один активный инвайт на пользователя в организацию
            $table->unique(['organization_id', 'user_id', 'status'], 'org_user_invitation_unique');
            
            // Индексы для быстрого поиска
            $table->index('organization_id');
            $table->index('user_id');
            $table->index('invited_by');
            $table->index('status');
            $table->index('expires_at');
            
            // Foreign keys
            $table->foreign('organization_id')->references('id')->on('tasks_management.organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks_management.organization_invitations');
    }
};