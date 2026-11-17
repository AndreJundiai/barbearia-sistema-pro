<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'active')) {
                $table->boolean('active')->default(true);
            }
            if (! Schema::hasColumn('services', 'featured')) {
                $table->boolean('featured')->default(false);
            }
            if (! Schema::hasColumn('services', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
        });

        Schema::table('hairdressers', function (Blueprint $table) {
            if (! Schema::hasColumn('hairdressers', 'active')) {
                $table->boolean('active')->default(true);
            }
            if (! Schema::hasColumn('hairdressers', 'instagram')) {
                $table->string('instagram')->nullable();
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'token')) {
                $table->string('token', 40)->nullable()->unique();
            }
            if (! Schema::hasColumn('appointments', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('appointments', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (! Schema::hasColumn('appointments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
        });

        // Gera tokens para agendamentos antigos (link de gerenciamento do cliente)
        \Illuminate\Support\Facades\DB::table('appointments')->whereNull('token')->orderBy('id')
            ->each(function ($row) {
                \Illuminate\Support\Facades\DB::table('appointments')
                    ->where('id', $row->id)
                    ->update(['token' => \Illuminate\Support\Str::random(40)]);
            });
    }

    public function down(): void
    {
        Schema::table('services', fn (Blueprint $t) => $t->dropColumn(['active', 'featured', 'sort_order']));
        Schema::table('hairdressers', fn (Blueprint $t) => $t->dropColumn(['active', 'instagram']));
        Schema::table('appointments', function (Blueprint $t) {
            $t->dropUnique(['token']);
            $t->dropColumn(['token', 'notes', 'completed_at', 'cancelled_at']);
        });
    }
};
