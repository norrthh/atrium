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
      Schema::create('promocode', function (Blueprint $table) {
         $table->id();
         $table->string('code');
         $table->json('type');
         $table->integer('countPrize')->nullable();
         $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
         $table->timestamps();
      });

      Schema::create('promocode_items', function (Blueprint $table) {
         $table->id();
         $table->integer('promocode_id');
         $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('promocode_defaults');
   }
};
