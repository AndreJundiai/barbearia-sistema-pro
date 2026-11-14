<?php

namespace Database\Seeders;

use App\Models\Hairdresser;
use Illuminate\Database\Seeder;

class HairdresserSeeder extends Seeder
{
    /** Equipe inicial — só é criada se ainda não houver profissionais. */
    public function run(): void
    {
        if (Hairdresser::exists()) {
            return;
        }

        $hairdressers = [
            ['name' => 'Marcello', 'specialty' => 'Master Barber', 'bio' => 'Especialista em cortes clássicos e barbas esculpidas com 15 anos de experiência.'],
            ['name' => 'Alexandro', 'specialty' => 'Corte & Barba', 'bio' => 'Focado em estilos modernos, degradês e design de sobrancelhas masculinas.'],
            ['name' => 'Vanderlei', 'specialty' => 'Clássico', 'bio' => 'A tradição da navalha e o cuidado com cada detalhe do visual masculino.'],
        ];

        foreach ($hairdressers as $hairdresser) {
            Hairdresser::create($hairdresser);
        }
    }
}
