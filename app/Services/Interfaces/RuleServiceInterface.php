<?php

namespace App\Services\Interfaces;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Collection;

interface RuleServiceInterface
{
    /**
     * Get all rules with filtering and pagination support
     */
    public function getAllRules(array $filters = []): Collection;

    /**
     * Get active rules for evaluation
     */
    public function getActiveRulesForEvaluation(): Collection;

    /**
     * Find a rule by ID
     */
    public function findRule(int $id): ?Rule;

    /**
     * Create a new rule with validation
     */
    public function createRule(array $data): Rule;

    /**
     * Update an existing rule
     */
    public function updateRule(int $id, array $data): Rule;

    /**
     * Delete a rule
     */
    public function deleteRule(int $id): bool;

    /**
     * Evaluate rules against line item and customer data
     */
    public function evaluateRules(array $lineData, array $customerData): array;

    /**
     * Validate rule condition and action JSON structure
     */
    public function validateRuleStructure(array $conditionJson, array $actionJson): bool;

    /**
     * Toggle rule active status
     */
    public function toggleRuleStatus(int $id): Rule;
} 