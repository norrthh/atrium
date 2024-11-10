<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPromocode extends Model
{
    protected $fillable = [
       'event_id',
       'code',
       'prize_id',
       'count',
       'count_used',
       'count_prize',
    ];
}
