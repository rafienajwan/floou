<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Models\Category;
use App\Models\PlantType;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan semua kategori dan tipe tanaman
        $categories = Category::all();
        $plantTypes = PlantType::all();

        // Buat array tanaman dengan data lengkap
        $baseTime = time();
        $plants = [
            [
                'name' => 'Monstera Deliciosa',
                'slug' => 'monstera-deliciosa',
                'description' => 'Tanaman hias dengan daun berbentuk unik dan berlubang. Cocok ditanam di dalam ruangan.',
                'price' => 150000,
                'stock' => 20,
                'category_id' => $categories->where('slug', 'tanaman-hias')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'indoor')->first()->id,
                'image' => 'plants/monstera-deliciosa-' . ($baseTime + 1) . '.jpg'
            ],
            [
                'name' => 'Aglonema Red',
                'slug' => 'aglonema-red',
                'description' => 'Tanaman hias berdaun merah yang cantik dan mudah perawatannya.',
                'price' => 85000,
                'stock' => 15,
                'category_id' => $categories->where('slug', 'tanaman-hias')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'indoor')->first()->id,
                'image' => 'plants/aglonema-red-' . ($baseTime + 2) . '.jpg'
            ],
            [
                'name' => 'Kaktus Mini',
                'slug' => 'kaktus-mini',
                'description' => 'Kaktus ukuran kecil yang cocok untuk meja kerja atau jendela.',
                'price' => 35000,
                'stock' => 30,
                'category_id' => $categories->where('slug', 'kaktus-sukulen')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'indoor')->first()->id,
                'image' => 'plants/kaktus-mini-' . ($baseTime + 3) . '.jpg'
            ],
            [
                'name' => 'Pohon Mangga',
                'slug' => 'pohon-mangga',
                'description' => 'Pohon mangga varietas harum manis yang sudah dapat berbuah.',
                'price' => 250000,
                'stock' => 8,
                'category_id' => $categories->where('slug', 'tanaman-buah')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'outdoor')->first()->id,
                'image' => 'plants/pohon-mangga-' . ($baseTime + 4) . '.jpg'
            ],
            [
                'name' => 'Lidah Buaya',
                'slug' => 'lidah-buaya',
                'description' => 'Tanaman obat yang memiliki banyak manfaat untuk kesehatan dan kecantikan.',
                'price' => 40000,
                'stock' => 25,
                'category_id' => $categories->where('slug', 'tanaman-obat')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'indoor')->first()->id,
                'image' => 'plants/lidah-buaya-' . ($baseTime + 5) . '.jpg'
            ],
            [
                'name' => 'Sirih Gading',
                'slug' => 'sirih-gading',
                'description' => 'Tanaman merambat dengan daun hijau mengkilap yang cantik.',
                'price' => 45000,
                'stock' => 18,
                'category_id' => $categories->where('slug', 'tanaman-hias')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'merambat')->first()->id,
                'image' => 'plants/sirih-gading-' . ($baseTime + 6) . '.jpg'
            ],
            [
                'name' => 'Teratai',
                'slug' => 'teratai',
                'description' => 'Tanaman air dengan bunga yang indah, cocok untuk kolam atau akuarium besar.',
                'price' => 65000,
                'stock' => 10,
                'category_id' => $categories->where('slug', 'tanaman-air')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'aquascape')->first()->id,
                'image' => 'plants/teratai-' . ($baseTime + 7) . '.jpg'
            ],
            [
                'name' => 'Sukulen Echeveria',
                'slug' => 'sukulen-echeveria',
                'description' => 'Tanaman sukulen berbentuk bunga mawar dengan warna hijau kebiruan.',
                'price' => 30000,
                'stock' => 40,
                'category_id' => $categories->where('slug', 'kaktus-sukulen')->first()->id,
                'plant_type_id' => $plantTypes->where('slug', 'indoor')->first()->id,
                'image' => 'plants/sukulen-echeveria-' . ($baseTime + 8) . '.jpg'
            ],
        ];

        // Masukkan data ke database
        foreach ($plants as $plant) {
            Plant::create($plant);
        }
    }
}
