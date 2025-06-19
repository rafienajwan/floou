<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@floou.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '081234567890',
            'address' => 'Jalan Admin No. 1, Jakarta'
        ]);

        // Regular customer (ubah 'customer' menjadi 'user')
        User::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user', // Ubah dari 'customer' menjadi 'user'
            'phone' => '082345678901',
            'address' => 'Jalan Customer No. 2, Jakarta'
        ]);

        // Generate more random users if needed
        User::factory(5)->create();
    }
}
