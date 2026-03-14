<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hairdressers', function (Blueprint $table) {
            if (!Schema::hasColumn('hairdressers', 'specialty')) {
                $table->string('specialty')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('hairdressers', 'bio')) {
                $table->text('bio')->nullable()->after('specialty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hairdressers', function (Blueprint $table) {
            $table->dropColumn(['specialty', 'bio']);
        });
    }
};
