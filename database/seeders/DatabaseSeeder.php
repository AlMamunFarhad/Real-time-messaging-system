<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Al Mamun',
            'email' => 'almamun@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        User::factory()->create([
            'name' => 'almamun2',
            'email' => 'almamun2@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
        ]);
    }
}
