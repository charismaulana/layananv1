<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['contractor_registration', 'movement', 'outside_meal']);
            $table->morphs('approvable'); // polymorphic: movements, outside_meals, users
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_super_approval')->default(false); // GS super approval
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->date('movement_date');
            $table->foreignId('from_region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('to_region_id')->constrained('regions')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['movement_date', 'status']);
        });

        Schema::create('movement_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // meal types affected by this movement
            $table->json('meal_type_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['movement_id', 'user_id']);
        });

        Schema::create('outside_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->date('meal_date');
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['meal_date', 'status']);
        });

        Schema::create('outside_meal_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outside_meal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['outside_meal_id', 'user_id']);
        });

        Schema::create('meal_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->constrained()->cascadeOnDelete();
            $table->date('meal_date');
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gs_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gs_user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('overridable'); // meal_plans, rosters
            $table->text('reason');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('overridden_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gs_overrides');
        Schema::dropIfExists('meal_cancellations');
        Schema::dropIfExists('outside_meal_people');
        Schema::dropIfExists('outside_meals');
        Schema::dropIfExists('movement_people');
        Schema::dropIfExists('movements');
        Schema::dropIfExists('approvals');
    }
};
