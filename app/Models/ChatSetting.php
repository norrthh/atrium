<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    protected $table = 'chat_settings';

    protected $fillable = ['welcome_message'];
}
