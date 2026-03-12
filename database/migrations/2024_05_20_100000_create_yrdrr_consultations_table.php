<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('yrdrr_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('face_shape')->nullable();
            $table->text('style_notes');
            $table->json('preferred_products')->nullable();
            $table->string('signature_fragrance')->nullable();
            $table->integer('experience_rating')->default(5);
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yrdrr_consultations');
    }
};