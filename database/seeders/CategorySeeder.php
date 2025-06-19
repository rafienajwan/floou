<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tanaman Hias', 'slug' => 'tanaman-hias'],
            ['name' => 'Tanaman Buah', 'slug' => 'tanaman-buah'],
            ['name' => 'Tanaman Obat', 'slug' => 'tanaman-obat'],
            ['name' => 'Kaktus & Sukulen', 'slug' => 'kaktus-sukulen'],
            ['name' => 'Tanaman Air', 'slug' => 'tanaman-air']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
