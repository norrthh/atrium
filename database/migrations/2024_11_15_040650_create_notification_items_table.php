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
      Schema::create('notification_items', function (Blueprint $table) {
         $table->id();
         $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
         $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
         $table->integer('count');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('notification_items');
   }
};
