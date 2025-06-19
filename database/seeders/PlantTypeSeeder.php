<?php

namespace Database\Seeders;

use App\Models\PlantType;
use Illuminate\Database\Seeder;

class PlantTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plantTypes = [
            ['name' => 'Indoor', 'slug' => 'indoor'],
            ['name' => 'Outdoor', 'slug' => 'outdoor'],
            ['name' => 'Merambat', 'slug' => 'merambat'],
            ['name' => 'Gantung', 'slug' => 'gantung'],
            ['name' => 'Aquascape', 'slug' => 'aquascape']
        ];

        foreach ($plantTypes as $type) {
            PlantType::create($type);
        }
    }
}
