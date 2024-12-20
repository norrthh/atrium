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
      Schema::create('chats', function (Blueprint $table) {
         $table->id();
         $table->string('chat_id')->nullable();
         $table->string('messanger')->nullable();
         $table->timestamps();
      });

      $chats = [
         [
            'chat_id' => env('VKONTAKTE_CHAT_ID'),
            'messanger' => 'vkontakte'
         ],
         [
            'chat_id' => env('TELEGRAM_CHANNEL_ID'),
            'messanger' => 'telegram'
         ],
      ];

      foreach ($chats as $chat) {
         \App\Models\Chats::query()->create($chat);
      }
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('chats');
   }
};
