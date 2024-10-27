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
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('count');
            $table->timestamps();
        });

        $coins = [5, 10, 15, 15, 15, 15, 15, 15, 15];

        foreach ($coins as $coin) {
            \App\Models\Coins::query()->create([
                'count' => $coin
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
