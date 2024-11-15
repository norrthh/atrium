<?php

namespace App\Models\Withdrawl;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WithdrawUsers extends Model
{
    protected $fillable = ['user_id', 'withdraw_items_id', 'status', 'count'];

    public function item(): HasOne
    {
        return $this->hasOne(WithdrawItems::class, 'id', 'withdraw_items_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
