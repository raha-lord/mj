<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем "Первую организацию" для существующих данных
        $firstOrganization = Organization::create([
            'name' => 'Первая организация',
            'description' => 'Организация для миграции существующих проектов и пользователей',
            'is_active' => true,
            'settings' => [
                'allow_public_projects' => false,
                'require_approval' => false,
                'default_role' => 'member'
            ]
        ]);

        echo "✅ Создана организация: {$firstOrganization->name}\n";

        // Привязываем все существующие проекты к первой организации
        $projectsCount = Project::whereNull('organization_id')->count();
        if ($projectsCount > 0) {
            Project::whereNull('organization_id')->update([
                'organization_id' => $firstOrganization->id
            ]);
            echo "✅ Привязано {$projectsCount} проектов к первой организации\n";
        }

        // Добавляем всех существующих пользователей в первую организацию как Members
        $users = User::where('is_super_user', false)->get();
        foreach ($users as $user) {
            $firstOrganization->users()->attach($user->id, [
                'role' => 'member',
                'joined_at' => now(),
                'notes' => 'Migrated from existing system'
            ]);
        }
        echo "✅ Добавлено {$users->count()} пользователей в первую организацию\n";

        // Создаем отдельного админа для первой организации (если есть пользователи)
        $firstUser = User::where('is_super_user', false)->first();
        if ($firstUser) {
            // Обновляем роль первого пользователя на org_admin
            $firstOrganization->users()->updateExistingPivot($firstUser->id, [
                'role' => 'org_admin',
                'notes' => 'Default admin for first organization'
            ]);
            echo "✅ Пользователь {$firstUser->name} назначен администратором первой организации\n";
        }

        echo "🎉 Миграция в организационную структуру завершена!\n";
    }
}
