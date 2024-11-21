<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

   protected $fillable = [
      'nickname',
      'telegram_id',
      'vkontakte_id',
      'coin',
      'avatar',
      'bilet',
      'username_vkontakte',
      'username_telegram',
      'avatar_telegram'
   ];

}
