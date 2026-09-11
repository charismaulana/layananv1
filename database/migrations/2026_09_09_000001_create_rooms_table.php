<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master kamar
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);             // Contoh: "Kamar 101", "A-12"
            $table->string('block', 50)->nullable();  // Blok/Gedung: "Blok A", "Mess Staff"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tambahkan kolom room_id ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('room_id')
                  ->nullable()
                  ->after('meal_location_id')
                  ->constrained('rooms')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });

        Schema::dropIfExists('rooms');
    }
};
