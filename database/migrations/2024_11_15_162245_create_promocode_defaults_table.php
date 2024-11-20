<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   /**
    * Run the migrations.
    */
   public function up(): void
   {
      Schema::create('promocodes', function (Blueprint $table) {
         $table->id();
         $table->string('code');
         $table->integer('promo_type');
         $table->json('expiration');
         $table->integer('countPrize')->nullable();
         $table->foreignId('event_id')->nullable()->constrained('events')->cascadeOnDelete();
         $table->timestamps();
      });

      Schema::create('promocode_items', function (Blueprint $table) {
         $table->id();
         $table->foreignId('promocode_id')->constrained('promocodes')->cascadeOnDelete();
         $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
         $table->integer('count')->default(0);
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('promocode_items');
      Schema::dropIfExists('promocodes');
   }
};
