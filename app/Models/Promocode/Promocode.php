<?php

namespace App\Models\Promocode;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promocode extends Model
{
   protected $fillable = [
      'code',
      'expiration',
      'promo_type',
      'countPrize',
      'event_id',
   ];
   protected $casts = [
      'expiration' => 'array',
   ];

   public function item(): HasMany
   {
      return $this->hasMany(PromocodeItem::class, 'promocode_id', 'id');
   }
}
