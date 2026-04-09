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
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        User::factory()->create([
            'name' => 'user2',
            'email' => 'user2@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
