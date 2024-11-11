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
      Schema::create('event_prizes', function (Blueprint $table) {
         $table->id();
         $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
         $table->foreignId('withdraw_items_id')->constrained('withdraw_items')->cascadeOnDelete();
         $table->integer('count_prize');
         $table->string('word')->nullable();
         $table->integer('status')->default(0);
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('event_prizes');
   }
};
