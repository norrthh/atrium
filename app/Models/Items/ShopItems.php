<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopItems extends Model
{
   protected $fillable = [
      'item_id',
      'item_type',
      'price',
      'count',
      'category',
      'countActivate',
      'status',
   ];

   protected $casts = [
      'item_type' => 'array',
   ];

   public function item(): HasOne
   {
      return $this->hasOne(Items::class, 'id', 'item_id');
   }
}
