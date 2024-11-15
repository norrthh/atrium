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
      Schema::create('tasks', function (Blueprint $table) {
         $table->id();
         $table->integer('typeSocial'); // 1 - telegram, 2 - vk
         $table->integer('typeTask'); // 1 - подписаться на тгк, 2 - подписаться на группу, 3 - вступить беседу тгк, 4 - вступить в беседу вк
         $table->string('href');
         $table->string('description'); // Например, Общение игроков
         $table->json('access');
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('tasks');
   }
};
