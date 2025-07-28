<?php

namespace App\Enums;

enum CustomerType: string
{
    case RETAIL = 'retail';
    case RESTAURANTS = 'restaurants';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum cases as options for validation
     */
    public static function rules(): string
    {
        return 'in:' . implode(',', self::values());
    }
} 