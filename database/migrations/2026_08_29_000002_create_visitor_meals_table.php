<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_meals', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('institution')->nullable(); // Instansi / Perusahaan / Keterangan Tamu
            $table->date('meal_date');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignId('meal_location_id')->constrained('meal_locations')->onDelete('cascade');
            $table->integer('pax_count')->default(1);
            $table->boolean('has_breakfast')->default(false);
            $table->boolean('has_lunch')->default(false);
            $table->boolean('has_dinner')->default(false);
            $table->boolean('has_supper')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('confirmed'); // confirmed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_meals');
    }
};
