<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'name',
        'salience',
        'stackable',
        'condition_json',
        'action_json',
        'is_active',
    ];

    protected $casts = [
        'salience' => 'integer',
        'stackable' => 'boolean',
        'is_active' => 'boolean',
        'condition_json' => 'array',
        'action_json' => 'array',
    ];

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by salience (priority).
     */
    public function scopeOrderBySalience($query)
    {
        return $query->orderBy('salience');
    }

    /**
     * Get formatted condition JSON.
     */
    public function getFormattedConditionAttribute(): array
    {
        return $this->condition_json ?? [];
    }

    /**
     * Get formatted action JSON.
     */
    public function getFormattedActionAttribute(): array
    {
        return $this->action_json ?? [];
    }
}
