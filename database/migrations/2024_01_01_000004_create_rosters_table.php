<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('roster_date');
            $table->enum('status', ['Kerja', 'Libur', 'Cuti'])->default('Kerja');
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'roster_date']);
            $table->index(['roster_date', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
