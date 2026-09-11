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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('breakfast_location_id')->nullable()->after('meal_location_id')->constrained('meal_locations')->nullOnDelete();
            $table->foreignId('lunch_location_id')->nullable()->after('breakfast_location_id')->constrained('meal_locations')->nullOnDelete();
            $table->foreignId('dinner_location_id')->nullable()->after('lunch_location_id')->constrained('meal_locations')->nullOnDelete();
            $table->foreignId('supper_location_id')->nullable()->after('dinner_location_id')->constrained('meal_locations')->nullOnDelete();
            $table->string('shift_type')->default('non_shift')->after('is_shift'); // non_shift, shift_pagi, shift_malam
        });

        Schema::table('rosters', function (Blueprint $table) {
            $table->string('shift_type')->nullable()->after('status'); // shift_pagi, shift_malam, non_shift
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rosters', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['breakfast_location_id']);
            $table->dropForeign(['lunch_location_id']);
            $table->dropForeign(['dinner_location_id']);
            $table->dropForeign(['supper_location_id']);
            $table->dropColumn([
                'breakfast_location_id',
                'lunch_location_id',
                'dinner_location_id',
                'supper_location_id',
                'shift_type',
            ]);
        });
    }
};
