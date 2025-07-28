<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\LoyaltyTier;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'email',
        'type',
        'loyalty_tier',
        'orders_count',
        'city',
    ];

    protected $casts = [
        'type' => CustomerType::class,
        'loyalty_tier' => LoyaltyTier::class,
        'orders_count' => 'integer',
    ];
}