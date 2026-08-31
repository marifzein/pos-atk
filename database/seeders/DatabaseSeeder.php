<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan seeder bawaan lainnya jika file-nya sudah kamu buat
        $this->call([
            // CategorySeeder::class,
            // ChartOfAccountSeeder::class,
            // ClientModuleSeeder::class, // Modul ON/OFF andalanmu
        ]);

        // Jalankan seeder user admin pertama di sini
        User::firstOrCreate(
            ['email' => 'super@gmail.com'],
            [
                'name' => 'Developer',
                'password' => Hash::make('87654321'),
                'role' => 'Admin', 
                'is_active' => 1 // Di-comment dulu kecuali kamu sudah tambahkan kolom ini di migration users
            ]
        );

       
    }
}