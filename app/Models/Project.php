<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'visibility',
        'organization_id'
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->whereNull('deleted_at');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Отношения к участникам проекта
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot(['role', 'joined_at'])
            ->whereNull('project_user.deleted_at')
            ->withTimestamps();
    }

    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    // Scopes
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->isSuperUser()) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Публичные проекты видят все
            $q->where('visibility', 'public')
              // Приватные проекты видят участники
              ->orWhereHas('users', function ($userQuery) use ($user) {
                  $userQuery->where('user_id', $user->id);
              })
              // Все проекты видят админы организации
              ->orWhereHas('organization.users', function ($orgQuery) use ($user) {
                  $orgQuery->where('user_id', $user->id)
                          ->where('role', 'org_admin');
              });
        })->whereIn('organization_id', function ($orgQuery) use ($user) {
            $orgQuery->select('organization_id')
                     ->from('organization_user')
                     ->where('user_id', $user->id)
                     ->whereNull('deleted_at');
        });
    }

    // Вспомогательные методы
    public function belongsToOrganization($organizationId): bool
    {
        return $this->organization_id == $organizationId;
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        // Проверяем принадлежность к организации
        if (!$user->belongsToOrganization($this->organization_id)) {
            return false;
        }

        // Админы организации видят все проекты
        if ($user->isOrgAdmin($this->organization_id)) {
            return true;
        }

        // Для публичных проектов достаточно быть в организации
        if ($this->visibility === 'public') {
            return true;
        }

        // Для приватных проектов нужно быть участником проекта
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function getUserRole(User $user): ?string
    {
        $projectUser = $this->users()->where('user_id', $user->id)->first();
        return $projectUser?->pivot?->role;
    }

    public function canUserManage(User $user): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        // Проверяем роль в организации
        $orgRole = $user->getOrganizationRole($this->organization_id);
        if (in_array($orgRole, ['org_admin'])) {
            return true;
        }

        // Проверяем роль в проекте
        return $this->getUserRole($user) === 'manager';
    }

    public function getMembersCount(): int
    {
        return $this->users()->count();
    }

    public function getActiveTasksCount(): int
    {
        return $this->tasks()
            ->whereNull('deleted_at')
            ->whereNull('completed_date')
            ->count();
    }
}