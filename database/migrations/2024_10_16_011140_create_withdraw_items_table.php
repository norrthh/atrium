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
        Schema::create('withdraw_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('price');
            $table->string('type');
            $table->timestamps();
        });

        $withdraws = [
            [
                'name' => 'ASTON MARTIN VINTAGE',
                'icon' => '/ayazik/car.png',
                'price' => '10 000',
                'type' => 'car'
            ],
            [
                'name' => 'ДЕВУШКА В ЧЁРНОМ ТОПЕ',
                'icon' => '/ayazik/woman.png',
                'price' => '10 000',
                'type' => 'skin'
            ],
            [
                'name' => 'АНГЕЛЬСКИЕ КРЫЛЬЯ',
                'icon' => '/ayazik/angel.png',
                'price' => '10 000',
                'type' => 'aks'
            ],
        ];

        foreach ($withdraws as $withdraw) {
            \App\Models\WithdrawItems::query()->create($withdraw);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_items');
    }
};
