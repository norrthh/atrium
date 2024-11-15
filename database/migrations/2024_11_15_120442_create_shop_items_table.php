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
        Schema::create('shop_items', function (Blueprint $table) {
           $table->id();
           $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
           $table->json('item_type'); // 1 - временный, 2 - вечный
           $table->integer('price');
           $table->integer('count');
           $table->integer('countActivate')->default(0);
           $table->integer('category');
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};
