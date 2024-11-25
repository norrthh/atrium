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
      Schema::create('user_mailing_logs', function (Blueprint $table) {
         $table->id();
         $table->foreignId('mailing_id')->nullable()->constrained('mailings')->cascadeOnDelete();
         $table->foreignId('telegraph_id')->nullable()->constrained('telegraph_chats')->cascadeOnDelete();
         $table->json('response');
         $table->integer('status')->default(0);
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('user_mailing_logs');
   }
};
