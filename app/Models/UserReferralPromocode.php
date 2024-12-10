<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserReferralPromocode extends Model
{
   protected $fillable = ['user_id', 'referral_promocode_id'];

   public function user(): HasOne
   {
      return $this->hasOne(User::class, 'id', 'user_id');
   }
}
