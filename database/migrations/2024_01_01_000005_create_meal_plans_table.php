<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('meal_date');
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_location_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'cancelled', 'outside_meal'])->default('active');
            $table->enum('source', ['roster', 'movement', 'manual'])->default('roster');
            $table->boolean('cancelled')->default(false);
            $table->boolean('outside_meal')->default(false);
            $table->foreignId('roster_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'meal_date', 'meal_type_id']);
            $table->index(['meal_date', 'region_id', 'meal_type_id']);
            $table->index(['meal_date', 'meal_location_id']);
            $table->index(['status', 'meal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
