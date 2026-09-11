<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default catering vendor name
        \Illuminate\Support\Facades\DB::table('app_settings')->insert([
            'key'         => 'catering_vendor_name',
            'value'       => 'PT Brylian Indah',
            'description' => 'Nama Perusahaan Mitra Penyedia Katering Aktif',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
