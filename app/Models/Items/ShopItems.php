<?php

namespace App\Models\Items;

use Illuminate\Database\Eloquent\Model;

class ShopItems extends Model
{
   protected $fillable = [
      'item_id',
      'item_type',
      'price',
      'count',
      'category',
   ];

   protected $casts = [
      'item_type' => 'array',
   ];
}
