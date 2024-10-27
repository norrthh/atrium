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
        Schema::create('event_korobkas', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->integer('countAttempt');
            $table->integer('countMessage');
            $table->json('bg');
            $table->enum('subscribe', ['required', 'optional']);
            $table->enum('subscribe_mailing', ['required', 'optional']);
            $table->integer('timeForAttempt');
            $table->json('cumebackPlayer');
            $table->text('text');
            $table->json('states');
            $table->json('attempts');
            $table->json('uploadStatus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_korobkas');
    }
};
