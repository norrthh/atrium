<?php

namespace App\Models\Promocode;

use Illuminate\Database\Eloquent\Model;

class PromocodeItem extends Model
{
   protected $fillable = [
      'promocode_id',
      'item_id',
   ];
}
