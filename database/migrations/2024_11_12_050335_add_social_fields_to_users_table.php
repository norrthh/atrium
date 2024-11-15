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
        Schema::table('users', function (Blueprint $table) {
           $table->string('username_vkontakte')->nullable();
           $table->string('username_telegram')->nullable()->after('username_vkontakte');
           $table->string('avatar_telegram')->nullable()->after('username_telegram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
           $table->dropColumn(['username_vkontakte', 'username_telegram', 'avatar_telegram']);
        });
    }
};
