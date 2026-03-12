<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarbershopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Service::create([
            'name' => 'Corte de Cabelo',
            'description' => 'Corte tradicional ou degradê completo.',
            'duration_minutes' => 45,
            'price' => 50.00,
        ]);

        \App\Models\Service::create([
            'name' => 'Barba Premium',
            'description' => 'Barba com toalha quente e massagem facial.',
            'duration_minutes' => 30,
            'price' => 35.00,
        ]);

        \App\Models\Service::create([
            'name' => 'Combo Elite (Corte + Barba)',
            'description' => 'A experiência completa de barbearia.',
            'duration_minutes' => 75,
            'price' => 75.00,
        ]);
    }
}
