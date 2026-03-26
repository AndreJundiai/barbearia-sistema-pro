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

        User::updateOrCreate(
            ['email' => 'adm@adm.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('4584'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'barbeiro1@example.com'],
            [
                'name' => 'Barbeiro 1',
                'password' => bcrypt('barber123'),
                'role' => 'barber',
            ]
        );

        User::updateOrCreate(
            ['email' => 'barbeiro2@example.com'],
            [
                'name' => 'Barbeiro 2',
                'password' => bcrypt('barber123'),
                'role' => 'barber',
            ]
        );

        $this->call([
            BarbershopSeeder::class,
            HairdresserSeeder::class,
        ]);
    }
}
