<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Roda a cada deploy (deployment/init.sh), por isso é idempotente:
     * só cria o que não existe e NUNCA sobrescreve senhas ou preços alterados no painel.
     */
    public function run(): void
    {
        if (! User::where('role', 'admin')->exists()) {
            User::firstOrCreate(
                ['email' => env('ADMIN_EMAIL', 'adm@adm.com')],
                [
                    'name' => 'Administrador',
                    'password' => bcrypt(env('ADMIN_PASSWORD', '4584')),
                    'role' => 'admin',
                ]
            );
        }

        $this->call([
            BarbershopSeeder::class,
            HairdresserSeeder::class,
        ]);
    }
}
