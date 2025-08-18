<?php
// app/Traits/HasAuditFields.php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasAuditFields
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditFields()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check() && $model->isSoftDeleting()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    /**
     * Check if model uses soft deletes
     */
    protected function isSoftDeleting(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::class));
    }

    /**
     * User who created the record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * User who last updated the record
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * User who deleted the record
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    /**
     * Scope to include audit information
     */
    public function scopeWithAudit($query)
    {
        return $query->with(['createdBy', 'updatedBy', 'deletedBy']);
    }
}