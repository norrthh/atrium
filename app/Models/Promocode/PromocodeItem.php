<?php

namespace App\Models\Promocode;

use App\Models\Items\Items;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromocodeItem extends Model
{
   protected $fillable = [
      'promocode_id',
      'item_id',
      'count',
   ];

   public function item(): \Illuminate\Database\Eloquent\Relations\HasOne
   {
      return $this->hasOne(Items::class, 'id', 'item_id');
   }
}
