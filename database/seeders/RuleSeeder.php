<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rules')->insert([
            [
                'id' => 100,
                'name' => 'Buy 5 Get 1 Free on SKU 123',
                'salience' => 10,
                'stackable' => false,
                'condition_json' => json_encode([
                    'operator' => 'AND',
                    'conditions' => [
                        ['field' => 'line.productId', 'operator' => '==', 'value' => 123],
                        ['field' => 'line.quantity', 'operator' => '>=', 'value' => 5]
                    ]
                ]),
                'action_json' => json_encode([
                    'type' => 'applyFreeUnits',
                    'args' => [1]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 101,
                'name' => 'Tiered Discount SKU 456',
                'salience' => 20,
                'stackable' => true,
                'condition_json' => json_encode([
                    'operator' => 'AND',
                    'conditions' => [
                        ['field' => 'line.productId', 'operator' => '==', 'value' => 456],
                        ['field' => 'line.quantity', 'operator' => '>=', 'value' => 5]
                    ]
                ]),
                'action_json' => json_encode([
                    'type' => 'applyTieredDiscount',
                    'tiers' => [
                        ['min_quantity' => 5, 'max_quantity' => 9, 'discount_percent' => 5],
                        ['min_quantity' => 10, 'max_quantity' => null, 'discount_percent' => 10]
                    ]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 102,
                'name' => '20% off Electronics',
                'salience' => 15,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'line.categoryId',
                    'operator' => '==',
                    'value' => 10
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [20]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 103,
                'name' => '10% off for Restaurants',
                'salience' => 30,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'customer.type',
                    'operator' => '==',
                    'value' => 'restaurants'
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [10]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 104,
                'name' => '5% off apple.com Corporate',
                'salience' => 25,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'customer.email',
                    'operator' => 'endsWith',
                    'value' => '@apple.com'
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [5]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 105,
                'name' => 'Flash Sale SKU 789',
                'salience' => 5,
                'stackable' => false,
                'condition_json' => json_encode([
                    'operator' => 'AND',
                    'conditions' => [
                        ['field' => 'line.productId', 'operator' => '==', 'value' => 789],
                        ['field' => 'now', 'operator' => '<', 'value' => '2025-07-01T00:00:00Z']
                    ]
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [25]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 106,
                'name' => 'Clearance Category Obsolete',
                'salience' => 40,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'line.categoryId',
                    'operator' => '==',
                    'value' => 99
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [50]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 107,
                'name' => 'Gold Tier Multiplier',
                'salience' => 35,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'customer.loyaltyTier',
                    'operator' => '==',
                    'value' => 'gold'
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [5]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 108,
                'name' => 'First Purchase SKU 555',
                'salience' => 12,
                'stackable' => true,
                'condition_json' => json_encode([
                    'operator' => 'AND',
                    'conditions' => [
                        ['field' => 'line.productId', 'operator' => '==', 'value' => 555],
                        ['field' => 'customer.ordersCount', 'operator' => '==', 'value' => 0]
                    ]
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [15]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 109,
                'name' => 'City Promo (Jeddah)',
                'salience' => 18,
                'stackable' => true,
                'condition_json' => json_encode([
                    'field' => 'customer.city',
                    'operator' => '==',
                    'value' => 'Jeddah'
                ]),
                'action_json' => json_encode([
                    'type' => 'applyPercent',
                    'args' => [3]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}