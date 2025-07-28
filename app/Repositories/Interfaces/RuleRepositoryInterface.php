<?php

namespace App\Repositories\Interfaces;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Collection;

interface RuleRepositoryInterface
{
    /**
     * Get all active rules ordered by salience
     */
    public function getAllActiveRules(): Collection;

    /**
     * Get all rules with optional filters
     */
    public function getAllRules(array $filters = []): Collection;

    /**
     * Find a rule by ID
     */
    public function findById(int $id): ?Rule;

    /**
     * Create a new rule
     */
    public function create(array $data): Rule;

    /**
     * Update a rule
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a rule
     */
    public function delete(int $id): bool;

    /**
     * Get rules by stackable status
     */
    public function getRulesByStackable(bool $stackable): Collection;

    /**
     * Get rules within salience range
     */
    public function getRulesBySalienceRange(int $min, int $max): Collection;
} 