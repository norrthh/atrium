<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventKorobka extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'countAttempt',
        'bg',
        'subscribe',
        'subscribe_mailing',
        'timeForAttempt',
        'cumebackPlayer',
        'text',
        'states',
        'attempts',
        'uploadStatus',
        'countMessage',
    ];

    protected $casts = [
        'bg' => 'array',
        'cumebackPlayer' => 'array',
        'states' => 'array',
        'attempts' => 'array',
        'uploadStatus' => 'array',
    ];
}
