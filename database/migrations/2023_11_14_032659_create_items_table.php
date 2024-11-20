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
      Schema::create('items', function (Blueprint $table) {
         $table->id();
         $table->integer('idItem');
         $table->integer('skin')->default(1);
         $table->string('name');
         $table->string('icon');
         $table->timestamps();
      });

      \App\Models\Items\Items::query()->create([
         'idItem' => 1,
         'name' => 'Монета',
         'icon' => 'ayazik/icons/coin.svg'
      ]);

      \App\Models\Items\Items::query()->create([
         'idItem' => 1,
         'name' => 'Билет',
         'icon' => 'ayazik/icons/bilet.svg'
      ]);
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('items');
   }
};
