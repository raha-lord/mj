<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserInvitationService
{
    /**
     * Создать нового пользователя без пароля (приглашение)
     */
    public function createInvitedUser(
        Organization $organization,
        string $email,
        string $name,
        string $role = 'member',
        User $inviter = null
    ): User {
        // Проверяем что пользователь с таким email не существует
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            throw new \InvalidArgumentException("User with email {$email} already exists");
        }

        // Создаем пользователя без пароля
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Временный случайный пароль
            'password_set_at' => null, // Указываем что пароль не установлен
            'is_super_user' => false,
        ]);

        // Добавляем в организацию
        $organization->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'notes' => $inviter 
                ? "Invited by {$inviter->name} ({$inviter->email})"
                : 'System invitation'
        ]);

        return $user;
    }

    /**
     * Добавить существующего пользователя в организацию
     */
    public function addExistingUserToOrganization(
        Organization $organization,
        string $email,
        string $role = 'member',
        User $inviter = null
    ): User {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \InvalidArgumentException("User with email {$email} does not exist");
        }

        // Проверяем что пользователь еще не в организации
        if ($organization->hasUser($user)) {
            throw new \InvalidArgumentException("User is already a member of this organization");
        }

        // Добавляем в организацию
        $organization->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'notes' => $inviter 
                ? "Added by {$inviter->name} ({$inviter->email})"
                : 'System addition'
        ]);

        return $user;
    }

    /**
     * Установить пароль для пользователя при первом входе
     */
    public function setFirstTimePassword(User $user, string $password): User
    {
        // Проверяем что у пользователя нет установленного пароля
        if ($user->hasPassword()) {
            throw new \InvalidArgumentException("User already has a password set");
        }

        $user->update([
            'password' => Hash::make($password),
            'password_set_at' => now(),
        ]);

        return $user->fresh();
    }

    /**
     * Проверить нужно ли пользователю установить пароль
     */
    public function needsPasswordSetup(User $user): bool
    {
        return !$user->hasPassword();
    }

    /**
     * Получить пользователей организации которые еще не установили пароль
     */
    public function getUsersWithoutPassword(Organization $organization): \Illuminate\Support\Collection
    {
        return $organization->users()
            ->whereNull('password_set_at')
            ->get();
    }

    /**
     * Найти или создать пользователя для приглашения
     */
    public function findOrCreateUser(
        Organization $organization,
        string $email,
        string $name,
        string $role = 'member',
        User $inviter = null
    ): array {
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Пользователь существует, добавляем в организацию
            if ($organization->hasUser($existingUser)) {
                return [
                    'user' => $existingUser,
                    'action' => 'already_member',
                    'message' => 'User is already a member of this organization'
                ];
            }

            $user = $this->addExistingUserToOrganization($organization, $email, $role, $inviter);
            return [
                'user' => $user,
                'action' => 'added_existing',
                'message' => 'Existing user added to organization'
            ];
        } else {
            // Создаем нового пользователя
            $user = $this->createInvitedUser($organization, $email, $name, $role, $inviter);
            return [
                'user' => $user,
                'action' => 'created_new',
                'message' => 'New user created and invited to organization'
            ];
        }
    }

    /**
     * Массовое приглашение пользователей
     */
    public function bulkInviteUsers(
        Organization $organization,
        array $invitations, // [['email' => '...', 'name' => '...', 'role' => '...']]
        User $inviter = null
    ): array {
        $results = [];
        
        foreach ($invitations as $invitation) {
            try {
                $result = $this->findOrCreateUser(
                    $organization,
                    $invitation['email'],
                    $invitation['name'],
                    $invitation['role'] ?? 'member',
                    $inviter
                );
                
                $results[] = array_merge($result, [
                    'success' => true,
                    'email' => $invitation['email']
                ]);
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'email' => $invitation['email'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Получить статистику приглашений организации
     */
    public function getInvitationStats(Organization $organization): array
    {
        $totalUsers = $organization->users()->count();
        $usersWithPassword = $organization->users()->whereNotNull('password_set_at')->count();
        $usersWithoutPassword = $totalUsers - $usersWithPassword;

        return [
            'total_users' => $totalUsers,
            'active_users' => $usersWithPassword,
            'pending_users' => $usersWithoutPassword,
            'completion_rate' => $totalUsers > 0 ? round(($usersWithPassword / $totalUsers) * 100, 1) : 0
        ];
    }

    /**
     * Отозвать приглашение (удалить пользователя который не установил пароль)
     */
    public function revokeInvitation(Organization $organization, User $user): bool
    {
        // Можно отозвать только если пользователь не установил пароль
        if ($user->hasPassword()) {
            throw new \InvalidArgumentException("Cannot revoke invitation for user who has already set password");
        }

        // Удаляем из организации
        $organization->users()->detach($user->id);

        // Если пользователь не состоит в других организациях, удаляем его совсем
        $otherOrganizations = $user->organizations()->where('organization_id', '!=', $organization->id)->count();
        
        if ($otherOrganizations === 0) {
            $user->delete();
            return true;
        }

        return false;
    }

    /**
     * Отправить напоминание о настройке пароля
     */
    public function sendPasswordSetupReminder(User $user): bool
    {
        if ($user->hasPassword()) {
            return false;
        }

        // Здесь можно добавить логику отправки email напоминания
        // Для простоты возвращаем true
        
        return true;
    }
}