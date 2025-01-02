<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatQuestion extends Model
{
    protected $fillable = ['question', 'answer'];
}
