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
      Schema::create('user_log_items', function (Blueprint $table) {
         $table->id();
         $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
         $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
         $table->foreignId('items_id')->constrained('items')->cascadeOnDelete();;
         $table->integer('count');
         $table->text('action');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('user_log_items');
   }
};
