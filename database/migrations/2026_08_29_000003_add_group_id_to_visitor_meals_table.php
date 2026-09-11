<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_meals', function (Blueprint $table) {
            $table->string('group_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('visitor_meals', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
