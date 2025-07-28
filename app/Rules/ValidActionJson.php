<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidActionJson implements ValidationRule
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

        if (!$this->validateActionStructure($value)) {
            $fail('The :attribute has an invalid action structure.');
        }
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
                return isset($action['args']) && 
                       is_array($action['args']) && 
                       count($action['args']) === 1 &&
                       is_numeric($action['args'][0]) &&
                       $action['args'][0] > 0;
            
            case 'applyFreeUnits':
                return isset($action['args']) && 
                       is_array($action['args']) && 
                       count($action['args']) === 1 &&
                       is_int($action['args'][0]) &&
                       $action['args'][0] > 0;
            
            case 'applyTieredDiscount':
                return isset($action['tiers']) && 
                       is_array($action['tiers']) && 
                       !empty($action['tiers']) &&
                       $this->validateTiers($action['tiers']);
            
            default:
                return false;
        }
    }

    /**
     * Validate tiered discount structure
     */
    private function validateTiers(array $tiers): bool
    {
        foreach ($tiers as $tier) {
            if (!is_array($tier) || 
                !isset($tier['min_quantity'], $tier['discount_percent']) ||
                !is_int($tier['min_quantity']) ||
                !is_numeric($tier['discount_percent']) ||
                $tier['min_quantity'] < 0 ||
                $tier['discount_percent'] <= 0 ||
                $tier['discount_percent'] > 100) {
                return false;
            }

            // max_quantity is optional but must be valid if present
            if (isset($tier['max_quantity']) && 
                (!is_int($tier['max_quantity']) || $tier['max_quantity'] <= $tier['min_quantity'])) {
                return false;
            }
        }

        return true;
    }
}
