<?php

namespace App\Enums;

enum LoyaltyTier: string
{
    case NONE = 'none';
    case SILVER = 'silver';
    case GOLD = 'gold';

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