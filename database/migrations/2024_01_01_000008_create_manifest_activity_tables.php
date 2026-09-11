<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifest_batches', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->date('manifest_date');
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_location_id')->constrained()->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->integer('total_pax')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['manifest_date', 'region_id', 'meal_type_id']);
        });

        Schema::create('manifest_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifest_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('sequence')->default(0);
            // Phase 2 fields (nullable in Phase 1)
            $table->enum('attendance_status', ['present', 'absent', 'unknown'])->default('unknown');
            $table->timestamp('signed_at')->nullable();
            $table->string('qr_scan_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type'); // login, logout, create_roster, approve_movement, etc.
            $table->string('module'); // auth, roster, meal_plan, movement, etc.
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->morphs('loggable'); // polymorphic: which model was affected
            $table->timestamps();
            // NO soft delete - audit trail tidak boleh dihapus

            $table->index(['user_id', 'activity_type']);
            $table->index(['module', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('manifest_people');
        Schema::dropIfExists('manifest_batches');
    }
};
