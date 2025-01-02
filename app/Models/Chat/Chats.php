<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $messanger
 * @property int $chat_id
 */
class Chats extends Model
{
    protected $fillable = [
        'messanger',
        'chat_id',
    ];
}
