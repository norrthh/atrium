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
        Schema::create('event_promocodes', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->nullable();
            $table->string('code')->nullable();
            $table->integer('prize_id')->nullable();
            $table->integer('count')->nullable();
            $table->integer('count_used')->nullable();
            $table->integer('count_prize')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_promocodes');
    }
};
