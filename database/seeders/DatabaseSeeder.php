<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the database with Products and Ingredients
        $beefId = \App\Models\Ingredients::factory()->create(['name' => 'BEEF', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $cheeseId = \App\Models\Ingredients::factory()->create(['name' => 'CHEESE', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;
        $onionId = \App\Models\Ingredients::factory()->create(['name' => 'ONION', 'total_weight' => 10000, 'remaining_weight' => 10000, 'weight_unit' => 'GRAM'])->id;

        \App\Models\Products::factory()->create([
            'name' => 'Burger',
            'ingredients' => [['id' => $beefId, 'weight' => 150], ['id' => $cheeseId, 'weight' => 30], ['id' => $onionId, 'weight' => 20]]
        ]);

    }
}