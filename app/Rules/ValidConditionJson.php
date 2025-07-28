<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidConditionJson implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('The :attribute must be a valid JSON object.');
            return;
        }

        if (!$this->validateConditionStructure($value)) {
            $fail('The :attribute has an invalid structure.');
        }
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
                if (!is_array($subCondition) || !$this->validateConditionStructure($subCondition)) {
                    return false;
                }
            }
            return true;
        }

        return false;
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