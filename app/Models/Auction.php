<?php

namespace App\Models;

use App\Models\Items\Items;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Auction extends Model
{
    protected $fillable = [
        'item_id',
        'item_type',
        'start_price',
        'time',
        'auction_end_time',
    ];

   protected $casts = [
      'item_type' => 'array',
   ];

   public function item(): HasOne
   {
      return $this->hasOne(Items::class, 'id', 'item_id');
   }
}
