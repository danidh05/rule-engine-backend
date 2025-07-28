<?php

namespace App\Repositories;

use App\Models\Rule;
use App\Repositories\Interfaces\RuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RuleRepository implements RuleRepositoryInterface
{
    public function __construct(
        protected Rule $model
    ) {}

    /**
     * Get all active rules ordered by salience
     */
    public function getAllActiveRules(): Collection
    {
        return $this->model
            ->active()
            ->orderBySalience()
            ->get();
    }

    /**
     * Get all rules with optional filters
     */
    public function getAllRules(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['stackable'])) {
            $query->where('stackable', $filters['stackable']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
        }

        return $query->orderBySalience()->get();
    }

    /**
     * Find a rule by ID
     */
    public function findById(int $id): ?Rule
    {
        return $this->model->find($id);
    }

    /**
     * Create a new rule
     */
    public function create(array $data): Rule
    {
        return $this->model->create($data);
    }

    /**
     * Update a rule
     */
    public function update(int $id, array $data): bool
    {
        $rule = $this->findById($id);
        
        if (!$rule) {
            return false;
        }

        return $rule->update($data);
    }

    /**
     * Delete a rule
     */
    public function delete(int $id): bool
    {
        $rule = $this->findById($id);
        
        if (!$rule) {
            return false;
        }

        return $rule->delete();
    }

    /**
     * Get rules by stackable status
     */
    public function getRulesByStackable(bool $stackable): Collection
    {
        return $this->model
            ->where('stackable', $stackable)
            ->active()
            ->orderBySalience()
            ->get();
    }

    /**
     * Get rules within salience range
     */
    public function getRulesBySalienceRange(int $min, int $max): Collection
    {
        return $this->model
            ->whereBetween('salience', [$min, $max])
            ->active()
            ->orderBySalience()
            ->get();
    }
} 