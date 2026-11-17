<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * O seeder antigo criava os mesmos serviços a cada deploy.
 * Mantém apenas o primeiro de cada nome ativo (os demais ficam desativados, sem apagar histórico).
 */
return new class extends Migration
{
    public function up(): void
    {
        $keep = DB::table('services')->selectRaw('MIN(id) as id')->groupBy('name')->pluck('id');

        DB::table('services')->whereNotIn('id', $keep)->update(['active' => false]);
    }

    public function down(): void
    {
        //
    }
};
