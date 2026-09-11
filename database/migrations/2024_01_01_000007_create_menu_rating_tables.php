<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->date('menu_date');
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['menu_date', 'region_id', 'meal_type_id']);
            $table->index(['menu_date', 'region_id']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // nasi, lauk, sayur, dll
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_type_id')->constrained()->cascadeOnDelete();
            $table->date('rating_date');
            $table->tinyInteger('score')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'menu_id']);
            $table->index(['rating_date', 'region_id']);
        });

        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', ['Menu', 'Rasa', 'Porsi', 'Distribusi', 'Kebersihan', 'Lainnya']);
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('meal_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_cards');
        Schema::dropIfExists('suggestions');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
