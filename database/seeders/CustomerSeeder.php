<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            [
                'id' => 1,
                'email' => 'alice@apple.com',
                'type' => 'restaurants',
                'loyalty_tier' => 'silver',
                'orders_count' => 3,
                'city' => 'Riyadh',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'email' => 'bob@techcorp.io',
                'type' => 'retail',
                'loyalty_tier' => 'gold',
                'orders_count' => 15,
                'city' => 'Jeddah',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'email' => 'carol@diner.sa',
                'type' => 'restaurants',
                'loyalty_tier' => 'none',
                'orders_count' => 0,
                'city' => 'Jeddah',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'email' => 'dave@example.com',
                'type' => 'retail',
                'loyalty_tier' => 'gold',
                'orders_count' => 7,
                'city' => 'Tabuk',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}