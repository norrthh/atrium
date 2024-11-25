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
         $table->integer('coins_week')->default(0);
         $table->boolean('isPremium')->default(false);
         $table->timestamp('updated_premium_at')->nullable()->after('updated_at');
      });

      $users = \App\Models\User\User::query()->get();

      foreach ($users as $user) {
         \App\Models\User\User::query()->where('id', $user->id)->update([
            'coins_week' => $user->coin
         ]);
      }
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::table('users', function (Blueprint $table) {
         $table->dropColumn('coins_week');
         $table->dropColumn('isPremium');
         $table->dropColumn('updated_premium_at');
      });
   }
};
