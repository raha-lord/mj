<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    // Scopes
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
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

        return $user->belongsToOrganization($this->organization_id);
    }
}