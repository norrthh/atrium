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
      Schema::create('events', function (Blueprint $table) {
         $table->id();
         $table->integer('post_id')->nullable();
         $table->enum('social_type', ['telegram', 'vk']);
         $table->integer('eventType');
         $table->string('word');
         $table->integer('countAttempt'); // Количество попыток:
         $table->integer('countMessage'); // Количество попыток до выпадения приза:
         $table->json('bg'); // картинки
         $table->enum('subscribe', ['required', 'optional']); // Подписка:
         $table->enum('subscribe_mailing', ['required', 'optional']); //Подписка рассылка
         $table->integer('timeForAttempt'); // Время между попытками:
         $table->json('cumebackPlayer'); // Возвращать игроков в конкурс бонусными попытками:
         $table->text('text'); // Пост в соц.сети
         $table->json('states'); // призы
         $table->json('attempts'); // призы
         $table->text('postMessage')->nullable();
         $table->integer('status')->default(0);
         $table->timestamps();
      });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
      Schema::dropIfExists('events');
   }
};
