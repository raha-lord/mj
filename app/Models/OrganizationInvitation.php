<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationInvitation extends Model
{
    use HasFactory;

    protected $table = 'tasks_management.organization_invitations';

    protected $fillable = [
        'organization_id',
        'user_id',
        'invited_by',
        'role',
        'status',
        'message',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Связь с организацией
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Связь с приглашенным пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с пользователем, который отправил приглашение
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Проверить действительно ли приглашение
     */
    public function isValid(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Принять приглашение
     */
    public function accept(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Проверяем что пользователь еще не состоит в организации
        if ($this->organization->hasUser($this->user)) {
            return false;
        }

        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        // Добавляем пользователя в организацию
        $this->organization->users()->attach($this->user_id, [
            'role' => $this->role,
            'joined_at' => now(),
            'notes' => "Accepted invitation from {$this->inviter->name} ({$this->inviter->email})",
        ]);

        return true;
    }

    /**
     * Отклонить приглашение
     */
    public function decline(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);

        return true;
    }

    /**
     * Отозвать приглашение
     */
    public function revoke(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->delete();
        return true;
    }

    /**
     * Scope для активных приглашений
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope для приглашений пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для приглашений организации
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}