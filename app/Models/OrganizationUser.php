<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationUser extends Model
{
    use HasFactory, SoftDeletes, HasAuditFields;

    protected $table = 'organization_user';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'joined_at',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Отношения
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'org_admin');
    }

    public function scopeProjectManagers($query)
    {
        return $query->where('role', 'project_manager');
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    // Вспомогательные методы
    public function isAdmin(): bool
    {
        return $this->role === 'org_admin';
    }

    public function isProjectManager(): bool
    {
        return in_array($this->role, ['org_admin', 'project_manager']);
    }

    public function isMember(): bool
    {
        return !empty($this->role);
    }
}
