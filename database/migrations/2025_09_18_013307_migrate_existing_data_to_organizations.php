<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Organization;
use App\Models\Project;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Создаем дефолтную организацию для существующих данных
        $defaultOrganization = Organization::create([
            'name' => 'Default Organization',
            'description' => 'Организация по умолчанию для существующих данных',
            'is_active' => true,
            'settings' => [
                'allow_public_projects' => false,
                'require_approval' => false,
                'default_role' => 'member'
            ]
        ]);

        // Привязываем всех существующих пользователей к дефолтной организации
        $users = User::all();
        foreach ($users as $user) {
            // Проверяем, не привязан ли уже пользователь к какой-либо организации
            if ($user->organizations()->count() === 0) {
                $role = $user->is_super_user ? 'super_user' : 'member';
                
                $user->organizations()->attach($defaultOrganization->id, [
                    'role' => $role,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Привязываем все существующие проекты к дефолтной организации
        $projects = Project::whereNull('organization_id')->get();
        foreach ($projects as $project) {
            $project->update(['organization_id' => $defaultOrganization->id]);
        }

        // Логируем результат миграции
        $userCount = $users->count();
        $projectCount = $projects->count();
        
        info("Migration completed: {$userCount} users and {$projectCount} projects migrated to default organization");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Находим дефолтную организацию
        $defaultOrganization = Organization::where('name', 'Default Organization')->first();
        
        if ($defaultOrganization) {
            // Отвязываем пользователей от дефолтной организации
            DB::table('tasks_management.organization_user')
                ->where('organization_id', $defaultOrganization->id)
                ->delete();
            
            // Отвязываем проекты от дефолтной организации
            Project::where('organization_id', $defaultOrganization->id)
                ->update(['organization_id' => null]);
            
            // Удаляем дефолтную организацию
            $defaultOrganization->delete();
        }
    }
};
