<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Проверяем есть ли уже супер пользователь
        $existingSuperUser = User::where('is_super_user', true)->first();
        
        if ($existingSuperUser) {
            echo "✅ Супер пользователь уже существует: {$existingSuperUser->email}\n";
            return;
        }

        // Создаем первого супер пользователя
        $superUser = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@mj-tasks.com',
            'password' => Hash::make('superadmin123'),
            'is_super_user' => true,
            'password_set_at' => now(),
            'email_verified_at' => now(),
        ]);

        echo "✅ Создан супер пользователь: {$superUser->email}\n";
        echo "📧 Email: {$superUser->email}\n";
        echo "🔑 Password: superadmin123\n";
        echo "⚠️  Рекомендуется сменить пароль после первого входа!\n";

        // Альтернативно можно сделать первого зарегистрированного пользователя супер админом
        $firstUser = User::where('is_super_user', false)->orderBy('created_at')->first();
        if ($firstUser && !$existingSuperUser) {
            echo "ℹ️  Первый пользователь {$firstUser->name} ({$firstUser->email}) также может быть назначен супер админом\n";
            echo "ℹ️  Для этого выполните: User::find({$firstUser->id})->update(['is_super_user' => true]);\n";
        }
    }
}
