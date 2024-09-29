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

        User::create([
            'name' => 'Lika Jean Sagunto Bernardino',
            'username' => 'ljsbernardino',
            'password' => bcrypt('ljs123'),
        ]);
        User::create([
            'name' => 'Nicole Wee Castillo',
            'username' => 'nwcastillo',
            'password' => bcrypt('nw123'),
        ]);
    }
}
