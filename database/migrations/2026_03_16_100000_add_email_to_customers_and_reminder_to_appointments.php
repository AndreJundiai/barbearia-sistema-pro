<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('reminder_sent')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('reminder_sent');
        });
    }
};
