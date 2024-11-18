<?php

namespace App\Models;

use App\Models\Items\Items;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WithdrawUsers extends Model
{
    protected $fillable = ['user_id', 'item_id', 'status', 'count'];

    public function item(): HasOne
    {
        return $this->hasOne(Items::class, 'id', 'item_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
