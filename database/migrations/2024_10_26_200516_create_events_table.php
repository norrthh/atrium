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
         $table->enum('social_type', ['telegram', 'vk'])->nullable();
         $table->integer('eventType')->nullable();
         $table->string('word')->nullable();
         $table->integer('countAttempt')->nullable(); // Количество попыток:
         $table->integer('countMessage')->nullable(); // Количество попыток до выпадения приза:
         $table->json('bg')->nullable(); // картинки
         $table->enum('subscribe', ['required', 'not_required'])->nullable(); // Подписка:
         $table->enum('subscribe_mailing', ['required', 'not_required'])->nullable(); //Подписка рассылка
         $table->integer('timeForAttempt')->nullable(); // Время между попытками:
         $table->json('cumebackPlayer')->nullable(); // Возвращать игроков в конкурс бонусными попытками:
         $table->text('text')->nullable(); // Пост в соц.сети
         $table->json('states')->nullable(); // призы
         $table->json('attempts')->nullable(); // призы
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
