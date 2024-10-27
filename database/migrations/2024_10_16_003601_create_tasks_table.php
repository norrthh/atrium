<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('icon');
            $table->string('title');
            $table->string('count');
            $table->string('url');
            $table->timestamps();
        });

        $tasks = [
            [
                'icon' => '/ayazik/telegram.svg',
                'title' => "<b>ПОДПИСАТЬСЯ НА</b> <br> ТЕЛЕГРАМ КАНАЛ ПРОЕКТА",
                'count' => 15,
                'url' => 'https://t.me/norrthh'
            ],
            [
                'icon' => '/ayazik/VK.svg',
                'title' => "<b>ПОДПИСАТЬСЯ НА ГРУППУ</b> <br> ATRIUM — СОТРУДНИЧЕСТВО",
                'count' => 15,
                'url' => 'https://t.me/norrthh'
            ],
            [
                'icon' => '/ayazik/VK.svg',
                'title' => "<b>ПОДПИСАТЬСЯ НА ГРУППУ</b> <br> МАСТЕРСКАЯ РАЗРАБОТЧИКА",
                'count' => 15,
                'url' => 'https://t.me/norrthh'
            ],
            [
                'icon' => '/ayazik/VK.svg',
                'title' => "<b>ВСТУПИТЬ В БЕСЕДУ</b> <br> ОБЩЕНИЕ ИГРОКОВ #1",
                'count' => 15,
                'url' => 'https://t.me/norrthh'
            ],
        ];

        foreach ($tasks as $task) {
            \App\Models\Tasks::query()->create($task);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
