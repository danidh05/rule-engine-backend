<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['id' => 10, 'name' => 'Electronics', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'name' => 'Home', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 99, 'name' => 'Clearance', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}