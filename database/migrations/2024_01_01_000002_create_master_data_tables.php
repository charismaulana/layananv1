<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('worker_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Pekerja, TA, TKJP, Sub Contractor
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ramba, Bentayan, Mangunjaya, Kluang
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('meal_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Breakfast, Lunch, Dinner, Supper, Snack
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('shift_only')->default(false); // Supper default untuk shift only
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('meal_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Mess Hall Staff, Mess Hall Nonstaff, Kantor, SP
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cutoff_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->integer('cutoff_days_before')->default(1); // H-1
            $table->time('cutoff_time')->default('19:00:00'); // 19:00 WIB
            $table->string('timezone')->default('Asia/Jakarta');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cutoff_settings');
        Schema::dropIfExists('meal_locations');
        Schema::dropIfExists('meal_types');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('worker_statuses');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('companies');
    }
};
