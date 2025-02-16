<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'kode_pegawai' => '198809102020030311000',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('EGovK4L*'),
        ]);
    }
}
