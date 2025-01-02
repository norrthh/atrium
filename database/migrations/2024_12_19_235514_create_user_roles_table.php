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
      Schema::create('user_roles', function (Blueprint $table) {
         $table->id();
         $table->string('vkontakte_id')->nullable();
         $table->string('telegram_id')->nullable();
         $table->integer('role');
         $table->timestamps();
      });

      \App\Models\User\UserRole::query()->create([
         'telegram_id' => 891954506,
         'role' => 2
      ]);
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('user_roles');
   }
};
