<?php

namespace App\Services;

use App\Models\Rule;
use App\Repositories\Interfaces\RuleRepositoryInterface;
use App\Services\Interfaces\RuleServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RuleService implements RuleServiceInterface
{
    public function __construct(
        protected RuleRepositoryInterface $ruleRepository
    ) {}

    /**
     * Get all rules with filtering and pagination support
     */
    public function getAllRules(array $filters = []): Collection
    {
        Log::info('Fetching rules with filters', ['filters' => $filters]);
        return $this->ruleRepository->getAllRules($filters);
    }

    /**
     * Get active rules for evaluation
     */
    public function getActiveRulesForEvaluation(): Collection
    {
        return $this->ruleRepository->getAllActiveRules();
    }

    /**
     * Find a rule by ID
     */
    public function findRule(int $id): ?Rule
    {
        $rule = $this->ruleRepository->findById($id);
        
        if (!$rule) {
            Log::warning('Rule not found', ['rule_id' => $id]);
        }
        
        return $rule;
    }

    /**
     * Create a new rule with validation
     */
    public function createRule(array $data): Rule
    {
        Log::info('Creating new rule', ['rule_name' => $data['name'] ?? 'Unknown']);

        // Validate JSON structure
        if (!$this->validateRuleStructure($data['condition_json'], $data['action_json'])) {
            throw new InvalidArgumentException('Invalid rule condition or action structure');
        }

        $rule = $this->ruleRepository->create($data);
        
        Log::info('Rule created successfully', ['rule_id' => $rule->id, 'rule_name' => $rule->name]);
        
        return $rule;
    }

    /**
     * Update an existing rule
     */
    public function updateRule(int $id, array $data): Rule
    {
        Log::info('Updating rule', ['rule_id' => $id]);

        $rule = $this->findRule($id);
        if (!$rule) {
            throw new ModelNotFoundException("Rule with ID {$id} not found");
        }

        // Validate JSON structure if provided
        if (isset($data['condition_json']) || isset($data['action_json'])) {
            $conditionJson = $data['condition_json'] ?? $rule->condition_json;
            $actionJson = $data['action_json'] ?? $rule->action_json;
            
            if (!$this->validateRuleStructure($conditionJson, $actionJson)) {
                throw new InvalidArgumentException('Invalid rule condition or action structure');
            }
        }

        $updated = $this->ruleRepository->update($id, $data);
        
        if (!$updated) {
            throw new \RuntimeException("Failed to update rule with ID {$id}");
        }

        $updatedRule = $this->findRule($id);
        Log::info('Rule updated successfully', ['rule_id' => $id]);
        
        return $updatedRule;
    }

    /**
     * Delete a rule
     */
    public function deleteRule(int $id): bool
    {
        Log::info('Deleting rule', ['rule_id' => $id]);

        $rule = $this->findRule($id);
        if (!$rule) {
            throw new ModelNotFoundException("Rule with ID {$id} not found");
        }

        $deleted = $this->ruleRepository->delete($id);
        
        if ($deleted) {
            Log::info('Rule deleted successfully', ['rule_id' => $id]);
        } else {
            Log::error('Failed to delete rule', ['rule_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Evaluate rules against line item and customer data
     */
    public function evaluateRules(array $lineData, array $customerData): array
    {
        Log::info('Starting rule evaluation', [
            'line_data' => $lineData,
            'customer_data' => $customerData
        ]);

        try {
            // Get active rules for evaluation
            $rules = $this->getActiveRulesForEvaluation();
            
            Log::info('Retrieved rules for evaluation', ['rule_count' => $rules->count()]);

            // Prepare payload for microservice
            $payload = [
                'line' => $lineData,
                'customer' => $customerData,
                'rules' => $rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'salience' => $rule->salience,
                        'stackable' => $rule->stackable,
                        'condition_json' => $rule->condition_json,
                        'action_json' => $rule->action_json,
                    ];
                })->toArray()
            ];

            // Send to microservice
            $response = Http::timeout(config('services.rule_engine.timeout', 30))
                ->post(config('services.rule_engine.url') . '/api/evaluate', $payload);

            if ($response->failed()) {
                Log::error('Rule engine service failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new RequestException($response);
            }

            $result = $response->json();
            
            Log::info('Rule evaluation completed', [
                'applied_rules_count' => count($result['applied'] ?? []),
                'total_discount' => $result['totalDiscount'] ?? 0,
                'final_total' => $result['finalLineTotal'] ?? 0
            ]);

            return $result;

        } catch (RequestException $e) {
            Log::error('Rule engine service request failed', [
                'error' => $e->getMessage(),
                'payload' => $payload ?? null
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rule evaluation failed', [
                'error' => $e->getMessage(),
                'line_data' => $lineData,
                'customer_data' => $customerData
            ]);
            throw $e;
        }
    }

    /**
     * Validate rule condition and action JSON structure
     */
    public function validateRuleStructure(array $conditionJson, array $actionJson): bool
    {
        // Validate condition structure
        if (!$this->validateConditionStructure($conditionJson)) {
            Log::warning('Invalid condition structure', ['condition' => $conditionJson]);
            return false;
        }

        // Validate action structure
        if (!$this->validateActionStructure($actionJson)) {
            Log::warning('Invalid action structure', ['action' => $actionJson]);
            return false;
        }

        return true;
    }

    /**
     * Toggle rule active status
     */
    public function toggleRuleStatus(int $id): Rule
    {
        $rule = $this->findRule($id);
        if (!$rule) {
            throw new ModelNotFoundException("Rule with ID {$id} not found");
        }

        $newStatus = !$rule->is_active;
        $this->ruleRepository->update($id, ['is_active' => $newStatus]);

        Log::info('Rule status toggled', [
            'rule_id' => $id,
            'old_status' => $rule->is_active,
            'new_status' => $newStatus
        ]);

        return $this->findRule($id);
    }

    /**
     * Validate condition JSON structure
     */
    private function validateConditionStructure(array $condition): bool
    {
        // Simple condition: field, operator, value
        if (isset($condition['field'], $condition['operator'], $condition['value'])) {
            return $this->validateField($condition['field']) && 
                   $this->validateOperator($condition['operator']);
        }

        // Complex condition: operator with conditions array
        if (isset($condition['operator'], $condition['conditions']) && 
            is_array($condition['conditions']) && 
            in_array($condition['operator'], ['AND', 'OR'])) {
            
            foreach ($condition['conditions'] as $subCondition) {
                if (!$this->validateConditionStructure($subCondition)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Validate action JSON structure
     */
    private function validateActionStructure(array $action): bool
    {
        if (!isset($action['type'])) {
            return false;
        }

        $validTypes = ['applyPercent', 'applyFreeUnits', 'applyTieredDiscount', 'applyFixedAmount'];
        
        if (!in_array($action['type'], $validTypes)) {
            return false;
        }

        // Validate based on action type
        switch ($action['type']) {
            case 'applyPercent':
            case 'applyFixedAmount':
                return isset($action['args']) && is_array($action['args']) && count($action['args']) === 1;
            
            case 'applyFreeUnits':
                return isset($action['args']) && is_array($action['args']) && count($action['args']) === 1;
            
            case 'applyTieredDiscount':
                return isset($action['tiers']) && is_array($action['tiers']) && !empty($action['tiers']);
            
            default:
                return false;
        }
    }

    /**
     * Validate field names
     */
    private function validateField(string $field): bool
    {
        $validFields = [
            'line.productId', 'line.quantity', 'line.categoryId', 'line.unitPrice',
            'customer.type', 'customer.email', 'customer.loyaltyTier', 
            'customer.ordersCount', 'customer.city', 'now'
        ];

        return in_array($field, $validFields);
    }

    /**
     * Validate operators
     */
    private function validateOperator(string $operator): bool
    {
        $validOperators = ['==', '!=', '>', '<', '>=', '<=', 'endsWith', 'startsWith', 'contains'];
        return in_array($operator, $validOperators);
    }
} 