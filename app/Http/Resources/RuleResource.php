<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $isDetailView = $request->route() && 
                       ($request->route()->getName() === 'rules.show' || 
                        str_contains($request->path(), 'rules/') && 
                        $request->isMethod('GET') && 
                        !str_contains($request->path(), 'toggle-status'));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'salience' => $this->salience,
            'stackable' => $this->stackable,
            'is_active' => $this->is_active,
            'condition' => $this->condition_json,
            'action' => $this->action_json,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Additional computed fields
            'priority_description' => $this->getPriorityDescription(),
            'type_description' => $this->getTypeDescription(),
            
            // Conditional fields for detailed view (show single rule)
            $this->mergeWhen($isDetailView, [
                'formatted_condition' => $this->getHumanReadableCondition(),
                'formatted_action' => $this->getHumanReadableAction(),
            ]),
        ];
    }

    /**
     * Get priority description based on salience value
     */
    private function getPriorityDescription(): string
    {
        return match (true) {
            $this->salience <= 10 => 'Very High',
            $this->salience <= 20 => 'High',
            $this->salience <= 30 => 'Medium',
            $this->salience <= 40 => 'Low',
            default => 'Very Low'
        };
    }

    /**
     * Get type description based on stackable property
     */
    private function getTypeDescription(): string
    {
        return $this->stackable ? 'Stackable' : 'Exclusive';
    }

    /**
     * Get human-readable condition description
     */
    private function getHumanReadableCondition(): string
    {
        $condition = $this->condition_json;
        
        if (!is_array($condition)) {
            return 'Invalid condition';
        }

        return $this->formatConditionRecursive($condition);
    }

    /**
     * Recursively format condition into human-readable text
     */
    private function formatConditionRecursive(array $condition): string
    {
        // Simple condition
        if (isset($condition['field'], $condition['operator'], $condition['value'])) {
            $field = $this->formatFieldName($condition['field']);
            $operator = $this->formatOperator($condition['operator']);
            $value = $this->formatValue($condition['value']);
            
            return "{$field} {$operator} {$value}";
        }

        // Complex condition
        if (isset($condition['operator'], $condition['conditions']) && is_array($condition['conditions'])) {
            $operator = strtolower($condition['operator']);
            $subconditions = array_map(
                fn($sub) => $this->formatConditionRecursive($sub),
                $condition['conditions']
            );
            
            return '(' . implode(" {$operator} ", $subconditions) . ')';
        }

        return 'Invalid condition format';
    }

    /**
     * Get human-readable action description
     */
    private function getHumanReadableAction(): string
    {
        $action = $this->action_json;
        
        if (!is_array($action) || !isset($action['type'])) {
            return 'Invalid action';
        }

        return match ($action['type']) {
            'applyPercent' => "Apply {$action['args'][0]}% discount",
            'applyFreeUnits' => "Add {$action['args'][0]} free unit(s)",
            'applyFixedAmount' => "Apply {$action['args'][0]} fixed discount",
            'applyTieredDiscount' => $this->formatTieredDiscount($action['tiers'] ?? []),
            default => 'Unknown action type'
        };
    }

    /**
     * Format tiered discount description
     */
    private function formatTieredDiscount(array $tiers): string
    {
        if (empty($tiers)) {
            return 'Invalid tiered discount';
        }

        $descriptions = array_map(function ($tier) {
            $min = $tier['min_quantity'];
            $max = $tier['max_quantity'] ?? '∞';
            $percent = $tier['discount_percent'];
            
            return "Qty {$min}-{$max}: {$percent}% off";
        }, $tiers);

        return 'Tiered discount: ' . implode(', ', $descriptions);
    }

    /**
     * Format field names for display
     */
    private function formatFieldName(string $field): string
    {
        return match ($field) {
            'line.productId' => 'Product ID',
            'line.quantity' => 'Quantity',
            'line.categoryId' => 'Category ID',
            'line.unitPrice' => 'Unit Price',
            'customer.type' => 'Customer Type',
            'customer.email' => 'Customer Email',
            'customer.loyaltyTier' => 'Loyalty Tier',
            'customer.ordersCount' => 'Orders Count',
            'customer.city' => 'Customer City',
            'now' => 'Current Time',
            default => $field
        };
    }

    /**
     * Format operators for display
     */
    private function formatOperator(string $operator): string
    {
        return match ($operator) {
            '==' => 'equals',
            '!=' => 'not equals',
            '>' => 'greater than',
            '<' => 'less than',
            '>=' => 'greater than or equal',
            '<=' => 'less than or equal',
            'endsWith' => 'ends with',
            'startsWith' => 'starts with',
            'contains' => 'contains',
            default => $operator
        };
    }

    /**
     * Format values for display
     */
    private function formatValue($value): string
    {
        if (is_string($value)) {
            return "\"{$value}\"";
        }
        
        return (string) $value;
    }

    /**
     * Get additional resource information
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}