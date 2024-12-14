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
      Schema::table('users', function (Blueprint $table) {
         $table->integer('admin')->default(0);
      });

      $users = [582127671, 217199523, 477629325, 232600787];

      foreach ($users as $user) {
          \App\Models\User\User::query()->where('vkontakte_id', $user)->update([
            'admin' => 1
          ]);
      }
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::table('users', function (Blueprint $table) {
         $table->dropColumn('admin');
      });
   }
};
