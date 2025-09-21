<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'organizations';

    protected $fillable = [
        'name',
        'description',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Отношения к пользователям
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['role', 'joined_at', 'notes'])
            ->whereNull('organization_user.deleted_at')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'org_admin');
    }

    public function projectManagers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'project_manager');
    }

    public function members(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'member');
    }

    // Отношения к проектам
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // Вспомогательные методы
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function getUserRole(User $user): ?string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;
        return $pivot?->role;
    }

    public function isAdmin(User $user): bool
    {
        return $this->getUserRole($user) === 'org_admin';
    }

    public function isProjectManager(User $user): bool
    {
        return in_array($this->getUserRole($user), ['org_admin', 'project_manager']);
    }

    public function isMember(User $user): bool
    {
        return $this->hasUser($user);
    }

    public function getActiveProjectsCount(): int
    {
        return $this->projects()->whereNull('deleted_at')->count();
    }

    public function getActiveUsersCount(): int
    {
        return $this->users()->count();
    }
}
