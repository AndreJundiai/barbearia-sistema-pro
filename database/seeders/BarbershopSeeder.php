<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class BarbershopSeeder extends Seeder
{
    /** Cria o catálogo inicial apenas se ainda não houver serviços cadastrados. */
    public function run(): void
    {
        if (Service::exists()) {
            return;
        }

        $services = [
            ['Corte de Cabelo', 'Corte tradicional ou degradê completo, com lavagem e finalização.', 45, 45.00, false],
            ['Barba Premium', 'Barba com toalha quente, navalha e massagem facial.', 30, 35.00, false],
            ['Combo Elite (Corte + Barba)', 'A experiência completa de barbearia. O mais pedido!', 75, 75.00, true],
            ['Sobrancelha', 'Design e acabamento na navalha.', 15, 15.00, false],
        ];

        foreach ($services as $i => [$name, $description, $duration, $price, $featured]) {
            Service::create([
                'name' => $name,
                'description' => $description,
                'duration_minutes' => $duration,
                'price' => $price,
                'featured' => $featured,
                'sort_order' => $i,
            ]);
        }
    }
}
